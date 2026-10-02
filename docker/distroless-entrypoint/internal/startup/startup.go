package startup

import (
	"errors"
	"fmt"
	"io"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
)

const app = "/var/www/html"

var SecretNames = []string{"APP_KEY", "DB_HOST", "DB_PORT", "DB_DATABASE", "DB_USERNAME", "DB_PASSWORD", "REDIS_HOST", "REDIS_PASSWORD", "REDIS_PORT", "MAIL_HOST", "MAIL_PORT", "MAIL_USERNAME", "MAIL_PASSWORD"}
var dataDirs = []string{
	"data/private_uploads", "data/private_uploads/assets", "data/private_uploads/accessories", "data/private_uploads/audits", "data/private_uploads/components", "data/private_uploads/consumables", "data/private_uploads/eula-pdfs", "data/private_uploads/imports", "data/private_uploads/models", "data/private_uploads/users", "data/private_uploads/licenses", "data/private_uploads/signatures",
	"data/uploads/accessories", "data/uploads/assets", "data/uploads/avatars", "data/uploads/barcodes", "data/uploads/categories", "data/uploads/companies", "data/uploads/components", "data/uploads/consumables", "data/uploads/departments", "data/uploads/locations", "data/uploads/maintenances", "data/uploads/manufacturers", "data/uploads/models", "data/uploads/suppliers", "dumps", "keys", "ssl",
}

type CommandFactory func(string, ...string) *exec.Cmd

// Initialize performs the existing Snipe-IT-specific startup sequence. It
// returns false when the successful APP_KEY display path intentionally skips
// long-running services.
func Initialize(makeCommand CommandFactory) (bool, error) {
	if makeCommand == nil {
		makeCommand = Command
	}
	if err := ResolveSecrets(); err != nil {
		return false, err
	}
	if os.Getenv("APP_KEY") == "" {
		fmt.Fprintln(os.Stderr, "Please re-run this container with an environment variable $APP_KEY")
		fmt.Fprintln(os.Stderr, "An example APP_KEY you could use is:")
		if err := makeCommand("/usr/bin/php", "artisan", "key:generate", "--show").Run(); err != nil {
			return false, err
		}
		return false, nil
	}
	for _, dir := range dataDirs {
		if err := os.MkdirAll(filepath.Join("/var/lib/snipeit", dir), 0755); err != nil {
			return false, err
		}
	}
	if err := os.MkdirAll("/run/snipeit/php", 0755); err != nil {
		return false, err
	}
	if err := RuntimeConfig(); err != nil {
		return false, err
	}
	if err := MigrationFiles(); err != nil {
		return false, err
	}
	for _, args := range [][]string{{"migrate", "--force"}, {"config:clear"}, {"config:cache"}, {"view:clear"}} {
		if err := makeCommand("/usr/bin/php", append([]string{"artisan"}, args...)...).Run(); err != nil {
			return false, fmt.Errorf("artisan %s: %w", strings.Join(args, " "), err)
		}
	}
	f, err := os.OpenFile(app+"/storage/logs/laravel.log", os.O_CREATE|os.O_APPEND, 0644)
	if err != nil {
		return false, err
	}
	f.Close()
	return true, nil
}

func ResolveSecrets() error {
	for _, name := range SecretNames {
		value, hasValue := os.LookupEnv(name)
		file, hasFile := os.LookupEnv(name + "_FILE")
		if hasValue && value != "" && hasFile && file != "" {
			return fmt.Errorf("error: both %s and %s_FILE are set (but are exclusive)", name, name)
		}
		if file != "" {
			b, err := os.ReadFile(file)
			if err != nil {
				return fmt.Errorf("read %s_FILE: %w", name, err)
			}
			if err := os.Setenv(name, strings.TrimRight(string(b), "\n")); err != nil {
				return err
			}
		}
		os.Unsetenv(name + "_FILE")
	}
	return nil
}

func RuntimeConfig() error {
	if value, ok := os.LookupEnv("PHP_UPLOAD_LIMIT"); ok {
		// Accept the same numeric value used by upstream; reject config injection.
		if value == "" || strings.Trim(value, "0123456789") != "" {
			return errors.New("PHP_UPLOAD_LIMIT must be a number")
		}
		data := fmt.Sprintf("upload_max_filesize = %sM\npost_max_size = %sM\n", value, value)
		if err := os.WriteFile("/run/snipeit/php/99-upload.ini", []byte(data), 0644); err != nil {
			return err
		}
	}
	limits := []struct{ name, directive string }{{"APACHE_LIMIT_REQUEST_FIELD_SIZE", "LimitRequestFieldSize"}, {"APACHE_LIMIT_REQUEST_LINE", "LimitRequestLine"}, {"APACHE_LIMIT_REQUEST_FIELDS", "LimitRequestFields"}}
	var lines strings.Builder
	for _, limit := range limits {
		if value, ok := os.LookupEnv(limit.name); ok {
			if value == "" || strings.Trim(value, "0123456789") != "" {
				return fmt.Errorf("%s must be a number", limit.name)
			}
			fmt.Fprintf(&lines, "%s %s\n", limit.directive, value)
		}
	}
	if err := os.WriteFile("/run/snipeit/limits.conf", []byte(lines.String()), 0644); err != nil {
		return err
	}
	cert, certErr := os.Stat("/var/lib/snipeit/ssl/snipeit-ssl.crt")
	key, keyErr := os.Stat("/var/lib/snipeit/ssl/snipeit-ssl.key")
	if certErr == nil && keyErr == nil && !cert.IsDir() && !key.IsDir() {
		return os.WriteFile("/run/snipeit/ssl.conf", []byte("Listen 8443\nInclude /etc/apache2/sites-available/001-default-ssl.conf\n"), 0644)
	}
	return os.WriteFile("/run/snipeit/ssl.conf", nil, 0644)
}

func MigrationFiles() error {
	return MigrationFilesAt(app, os.Getenv("SESSION_DRIVER"))
}

func MigrationFilesAt(root, sessionDriver string) error {
	// The upstream shell test uses a quoted glob, so it always copies these files.
	matches, err := filepath.Glob(filepath.Join(root, "vendor/laravel/passport/database/migrations/*"))
	if err != nil {
		return err
	}
	for _, src := range matches {
		if err := CopyMigration(src, filepath.Join(root, "database/migrations", filepath.Base(src))); err != nil {
			return err
		}
	}
	if sessionDriver == "database" {
		return CopyMigration(filepath.Join(root, "vendor/laravel/framework/src/Illuminate/Session/Console/stubs/database.stub"), filepath.Join(root, "database/migrations/2021_05_06_0000_create_sessions_table.php"))
	}
	return nil
}

func CopyMigration(src, dst string) error {
	in, err := os.Open(src)
	if err != nil {
		return err
	}
	defer in.Close()
	info, err := in.Stat()
	if err != nil {
		return err
	}
	if !info.Mode().IsRegular() {
		return nil
	}
	out, err := os.OpenFile(dst, os.O_CREATE|os.O_TRUNC|os.O_WRONLY, info.Mode().Perm())
	if err != nil {
		return err
	}
	if err := out.Chmod(info.Mode().Perm()); err != nil {
		out.Close()
		return err
	}
	_, err = io.Copy(out, in)
	closeErr := out.Close()
	if err != nil {
		return err
	}
	return closeErr
}

func Command(name string, args ...string) *exec.Cmd {
	cmd := exec.Command(name, args...)
	cmd.Dir = app
	cmd.Stdin, cmd.Stdout, cmd.Stderr = os.Stdin, os.Stdout, os.Stderr
	return cmd
}
