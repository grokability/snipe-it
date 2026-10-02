package startup

import (
	"os"
	"os/exec"
	"path/filepath"
	"reflect"
	"strings"
	"testing"
)

func TestStartupCommandFixture(t *testing.T) {
	if os.Getenv("STARTUP_COMMAND_FIXTURE") != "1" {
		return
	}
}

func TestInitializeReturnsNoServicesAfterSuccessfulKeyDisplay(t *testing.T) {
	t.Setenv("APP_KEY", "")
	for _, name := range SecretNames {
		t.Setenv(name+"_FILE", "")
	}
	var gotName string
	var gotArgs []string
	makeCommand := func(name string, args ...string) *exec.Cmd {
		gotName = name
		gotArgs = append([]string(nil), args...)
		cmd := exec.Command(os.Args[0], "-test.run=^TestStartupCommandFixture$")
		cmd.Env = append(os.Environ(), "STARTUP_COMMAND_FIXTURE=1")
		return cmd
	}

	startServices, err := Initialize(makeCommand)
	if err != nil {
		t.Fatalf("Initialize() error = %v", err)
	}
	if startServices {
		t.Fatal("successful key display requested long-running services")
	}
	if gotName != "/usr/bin/php" || !reflect.DeepEqual(gotArgs, []string{"artisan", "key:generate", "--show"}) {
		t.Fatalf("key display command = %q %q", gotName, gotArgs)
	}
}

func TestMigrationCopyOverwritesExistingUpstreamMigration(t *testing.T) {
	dir := t.TempDir()
	src := filepath.Join(dir, "upstream-migration.php")
	dst := filepath.Join(dir, "2021_05_06_0000_create_sessions_table.php")
	if err := os.WriteFile(src, []byte("upstream migration"), 0644); err != nil {
		t.Fatal(err)
	}
	if err := os.WriteFile(dst, []byte("existing migration"), 0644); err != nil {
		t.Fatal(err)
	}
	if err := CopyMigration(src, dst); err != nil {
		t.Fatal(err)
	}
	got, err := os.ReadFile(dst)
	if err != nil {
		t.Fatal(err)
	}
	if string(got) != "upstream migration" {
		t.Fatalf("migration content = %q, want upstream migration copied over the existing file", got)
	}
}

func TestMigrationFilesCopiesOAuthAndDatabaseSessionFilesOverExistingFiles(t *testing.T) {
	root := t.TempDir()
	oauthSource := filepath.Join(root, "vendor/laravel/passport/database/migrations/2016_06_01_oauth.php")
	oauthDest := filepath.Join(root, "database/migrations/2016_06_01_oauth.php")
	sessionSource := filepath.Join(root, "vendor/laravel/framework/src/Illuminate/Session/Console/stubs/database.stub")
	sessionDest := filepath.Join(root, "database/migrations/2021_05_06_0000_create_sessions_table.php")
	for path, content := range map[string]string{
		oauthSource: "upstream OAuth migration", oauthDest: "old OAuth migration",
		sessionSource: "upstream session migration", sessionDest: "old session migration",
	} {
		if err := os.MkdirAll(filepath.Dir(path), 0755); err != nil {
			t.Fatal(err)
		}
		if err := os.WriteFile(path, []byte(content), 0644); err != nil {
			t.Fatal(err)
		}
	}
	if err := MigrationFilesAt(root, "database"); err != nil {
		t.Fatal(err)
	}
	for path, want := range map[string]string{oauthDest: "upstream OAuth migration", sessionDest: "upstream session migration"} {
		got, err := os.ReadFile(path)
		if err != nil {
			t.Fatal(err)
		}
		if string(got) != want {
			t.Errorf("%s = %q, want %q", path, got, want)
		}
	}
}

func TestMigrationFilesLeavesSessionMigrationAloneForNonDatabaseDriver(t *testing.T) {
	root := t.TempDir()
	sessionSource := filepath.Join(root, "vendor/laravel/framework/src/Illuminate/Session/Console/stubs/database.stub")
	sessionDest := filepath.Join(root, "database/migrations/2021_05_06_0000_create_sessions_table.php")
	if err := os.MkdirAll(filepath.Dir(sessionSource), 0755); err != nil {
		t.Fatal(err)
	}
	if err := os.MkdirAll(filepath.Dir(sessionDest), 0755); err != nil {
		t.Fatal(err)
	}
	if err := os.WriteFile(sessionSource, []byte("upstream session migration"), 0644); err != nil {
		t.Fatal(err)
	}
	if err := os.WriteFile(sessionDest, []byte("existing session migration"), 0644); err != nil {
		t.Fatal(err)
	}
	if err := MigrationFilesAt(root, "file"); err != nil {
		t.Fatal(err)
	}
	got, err := os.ReadFile(sessionDest)
	if err != nil {
		t.Fatal(err)
	}
	if string(got) != "existing session migration" {
		t.Fatalf("session migration = %q, want existing file driver migration unchanged", got)
	}
}

func TestResolveSecretsLoadsSupportedFilesAndUnsetsFileVariables(t *testing.T) {
	dir := t.TempDir()
	for _, name := range SecretNames {
		secretFile := filepath.Join(dir, name)
		if err := os.WriteFile(secretFile, []byte("fixture-secret\n\n"), 0600); err != nil {
			t.Fatal(err)
		}
		t.Setenv(name, "")
		t.Setenv(name+"_FILE", secretFile)
	}
	if err := ResolveSecrets(); err != nil {
		t.Fatal(err)
	}
	for _, name := range SecretNames {
		if got := os.Getenv(name); got != "fixture-secret" {
			t.Errorf("%s = %q, want file contents without trailing newlines", name, got)
		}
		if _, ok := os.LookupEnv(name + "_FILE"); ok {
			t.Errorf("%s_FILE remains set after resolution", name)
		}
	}
}

func TestResolveSecretsRejectsConflictsWithoutExposingValues(t *testing.T) {
	secretFile := filepath.Join(t.TempDir(), "password")
	if err := os.WriteFile(secretFile, []byte("file-secret"), 0600); err != nil {
		t.Fatal(err)
	}
	t.Setenv("DB_PASSWORD", "environment-secret")
	t.Setenv("DB_PASSWORD_FILE", secretFile)
	err := ResolveSecrets()
	if err == nil {
		t.Fatal("expected conflicting secret configuration to fail")
	}
	if !strings.Contains(err.Error(), "DB_PASSWORD_FILE") {
		t.Fatalf("conflict error %q does not identify the conflicting variables", err)
	}
	if strings.Contains(err.Error(), "environment-secret") || strings.Contains(err.Error(), "file-secret") {
		t.Fatalf("conflict error exposed a secret value: %q", err)
	}
}
