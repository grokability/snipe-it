package supervisor

import (
	"context"
	"fmt"
	"os"
	"os/exec"
	"os/signal"
	"path/filepath"
	"strings"
	"syscall"
	"testing"
	"time"

	"snipeit-distroless-entrypoint/internal/handoff"
)

func TestInitBoundaryFixture(t *testing.T) {
	role := os.Getenv("INIT_BOUNDARY_ROLE")
	if role == "" && len(os.Args) > 1 {
		candidate := os.Args[len(os.Args)-1]
		if candidate == "apache" || candidate == "scheduler" {
			role = candidate
		}
	}
	if role == "" {
		return
	}
	marker := os.Getenv("INIT_BOUNDARY_MARKER")
	appendBoundaryMarker(t, marker, fmt.Sprintf("%s start pid=%d ppid=%d pgid=%d\n", role, os.Getpid(), os.Getppid(), syscall.Getpgrp()))
	if os.Getenv("LARGE_HANDOFF_VALUE") != "" {
		appendBoundaryMarker(t, marker, fmt.Sprintf("%s large-bytes=%d\n", role, len(os.Getenv("LARGE_HANDOFF_VALUE"))))
	}
	if role == "init" {
		switch os.Getenv("INIT_BOUNDARY_MODE") {
		case "fail":
			os.Exit(7)
		case "malformed":
			writer := os.NewFile(3, "result")
			_, _ = writer.Write([]byte("not-a-protocol-frame"))
			_ = writer.Close()
			return
		case "exit-success":
			if err := writeBoundaryResult(os.NewFile(3, "result"), handoff.Result{Disposition: handoff.ExitSuccess}); err != nil {
				t.Fatal(err)
			}
			return
		case "large":
			if err := writeBoundaryResult(os.NewFile(3, "result"), handoff.Result{Disposition: handoff.StartServices, Environment: []handoff.EnvironmentEntry{{Name: "INIT_BOUNDARY_MARKER", Value: marker}, {Name: "LARGE_HANDOFF_VALUE", Value: strings.Repeat("x", 96<<10)}}}); err != nil {
				t.Fatal(err)
			}
			return
		case "wait-for-term":
			writer := os.NewFile(3, "result")
			defer writer.Close()
			if _, err := writer.Write([]byte("SITP")); err != nil {
				t.Fatal(err)
			}
			signals := make(chan os.Signal, 1)
			signal.Notify(signals, syscall.SIGTERM, syscall.SIGINT)
			appendBoundaryMarker(t, marker, "init ready\n")
			<-signals
			appendBoundaryMarker(t, marker, "init signal\n")
			return
		default:
			if err := writeBoundaryResult(os.NewFile(3, "result"), boundaryStartResult(marker)); err != nil {
				t.Fatal(err)
			}
			return
		}
	}
	signals := make(chan os.Signal, 1)
	signal.Notify(signals, syscall.SIGTERM, syscall.SIGINT)
	<-signals
	appendBoundaryMarker(t, marker, fmt.Sprintf("%s signal\n", role))
	if os.Getenv("LARGE_HANDOFF_VALUE") != "" {
		appendBoundaryMarker(t, marker, fmt.Sprintf("%s large-bytes=%d\n", role, len(os.Getenv("LARGE_HANDOFF_VALUE"))))
	}
}

func TestRunWithInitSuccessStartsServicesAndStopsCleanly(t *testing.T) {
	marker := filepath.Join(t.TempDir(), "events")
	ctx, cancel := context.WithCancel(context.Background())
	done := make(chan error, 1)
	go func() {
		done <- runWithInit(ctx, boundaryInitCommand(marker, "success"), boundaryServiceFactory(marker), time.Hour, 3*time.Second, boundaryReaper())
	}()
	if !waitBoundaryPrefix(t, marker, "apache start", 5*time.Second) || !waitBoundaryPrefix(t, marker, "scheduler start", 5*time.Second) {
		cancel()
		select {
		case <-done:
		case <-time.After(time.Second):
		}
		t.Fatalf("init success did not start services: %v", readBoundaryLines(marker))
	}
	cancel()
	select {
	case err := <-done:
		if err != nil {
			t.Fatalf("supervisor shutdown = %v", err)
		}
	case <-time.After(2 * time.Second):
		t.Fatal("supervisor did not stop after cancellation")
	}
	lines := readBoundaryLines(marker)
	if !hasBoundaryLine(lines, "apache signal") || !hasBoundaryLine(lines, "scheduler signal") {
		t.Fatalf("service process groups were not stopped: %v", lines)
	}
	for _, role := range []string{"init", "apache", "scheduler"} {
		line := boundaryLine(lines, role+" start")
		if !strings.Contains(line, fmt.Sprintf("ppid=%d", os.Getpid())) {
			t.Errorf("%s was not a direct supervisor child: %s", role, line)
		}
		pid, pgid := boundaryID(line, "pid="), boundaryID(line, "pgid=")
		if pid <= 0 || pid != pgid {
			t.Errorf("%s did not own a dedicated process group: %s", role, line)
		}
	}
}

func TestRunWithInitFailureStartsNoServicesAndReturnsFailure(t *testing.T) {
	marker := filepath.Join(t.TempDir(), "events")
	err := runWithInit(context.Background(), boundaryInitCommand(marker, "fail"), boundaryServiceFactory(marker), time.Hour, 3*time.Second, boundaryReaper())
	if code, ok := InitFailureExitCode(err); !ok || code != 7 || !strings.Contains(err.Error(), "init exited") {
		t.Fatalf("runWithInit() error = %v, exit code=(%d,%v), want preserved status 7", err, code, ok)
	}
	for _, line := range readBoundaryLines(marker) {
		if strings.HasPrefix(line, "apache ") || strings.HasPrefix(line, "scheduler ") {
			t.Fatalf("service started after init failure: %v", readBoundaryLines(marker))
		}
	}
}

func TestRunWithInitRejectsMalformedResultAndStartsNoServices(t *testing.T) {
	marker := filepath.Join(t.TempDir(), "events")
	err := runWithInit(context.Background(), boundaryInitCommand(marker, "malformed"), boundaryServiceFactory(marker), time.Hour, 3*time.Second, boundaryReaper())
	if err == nil || !strings.Contains(err.Error(), "invalid init result") {
		t.Fatalf("malformed init result error = %v", err)
	}
	assertNoBoundaryServices(t, marker)
}

func TestRunWithInitExitSuccessStartsNoServices(t *testing.T) {
	marker := filepath.Join(t.TempDir(), "events")
	err := runWithInit(context.Background(), boundaryInitCommand(marker, "exit-success"), boundaryServiceFactory(marker), time.Hour, 3*time.Second, boundaryReaper())
	if err != nil {
		t.Fatalf("exit-success init result = %v", err)
	}
	assertNoBoundaryServices(t, marker)
}

func TestRunWithInitDrainsPayloadLargerThanPipeCapacity(t *testing.T) {
	marker := filepath.Join(t.TempDir(), "events")
	ctx, cancel := context.WithCancel(context.Background())
	done := make(chan error, 1)
	go func() {
		done <- runWithInit(ctx, boundaryInitCommand(marker, "large"), boundaryServiceFactory(marker), time.Hour, 3*time.Second, boundaryReaper())
	}()
	if !waitBoundaryPrefix(t, marker, "apache start", 5*time.Second) || !waitBoundaryPrefix(t, marker, "scheduler start", 5*time.Second) {
		cancel()
		select {
		case err := <-done:
			t.Fatalf("large init handoff did not start services: err=%v events=%v", err, readBoundaryLines(marker))
		case <-time.After(2 * time.Second):
			t.Fatalf("large init handoff hung: events=%v", readBoundaryLines(marker))
		}
	}
	if !waitBoundaryPrefix(t, marker, "scheduler large-bytes=98304", time.Second) {
		cancel()
		t.Fatalf("large environment value was not preserved into service process: %v", readBoundaryLines(marker))
	}
	cancel()
	select {
	case err := <-done:
		if err != nil {
			t.Fatalf("large-handoff shutdown: %v", err)
		}
	case <-time.After(2 * time.Second):
		t.Fatal("supervisor did not stop after large handoff")
	}
}

func assertNoBoundaryServices(t *testing.T, marker string) {
	t.Helper()
	for _, line := range readBoundaryLines(marker) {
		if strings.HasPrefix(line, "apache ") || strings.HasPrefix(line, "scheduler ") {
			t.Fatalf("services started when init did not request them: %v", readBoundaryLines(marker))
		}
	}
}

func TestRunWithInitShutdownDuringInitializationSuppressesServices(t *testing.T) {
	marker := filepath.Join(t.TempDir(), "events")
	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()
	done := make(chan error, 1)
	go func() {
		done <- runWithInit(ctx, boundaryInitCommand(marker, "wait-for-term"), boundaryServiceFactory(marker), time.Hour, 3*time.Second, boundaryReaper())
	}()
	if !waitBoundaryPrefix(t, marker, "init ready", time.Second) {
		cancel()
		t.Fatal("init fixture did not start")
	}
	cancel()
	select {
	case err := <-done:
		if err != nil {
			t.Fatalf("shutdown during init = %v", err)
		}
	case <-time.After(2 * time.Second):
		t.Fatalf("supervisor did not finish shutdown during init; events=%v", readBoundaryLines(marker))
	}
	lines := readBoundaryLines(marker)
	if !hasBoundaryLine(lines, "init signal") {
		t.Fatalf("termination was not forwarded to init process group: %v", lines)
	}
	for _, line := range lines {
		if strings.HasPrefix(line, "apache ") || strings.HasPrefix(line, "scheduler ") {
			t.Fatalf("service started after shutdown began: %v", lines)
		}
	}
}

func writeBoundaryResult(writer *os.File, result handoff.Result) error {
	if err := handoff.WriteResult(writer, result); err != nil {
		writer.Close()
		return err
	}
	return writer.Close()
}

func boundaryStartResult(marker string) handoff.Result {
	return handoff.Result{Disposition: handoff.StartServices, Environment: []handoff.EnvironmentEntry{{Name: "INIT_BOUNDARY_MARKER", Value: marker}}}
}

func boundaryInitCommand(marker, mode string) func(*os.File) *exec.Cmd {
	return func(writer *os.File) *exec.Cmd {
		cmd := exec.Command(os.Args[0], "-test.run=^TestInitBoundaryFixture$")
		cmd.Env = append(os.Environ(), "INIT_BOUNDARY_ROLE=init", "INIT_BOUNDARY_MODE="+mode, "INIT_BOUNDARY_MARKER="+marker)
		cmd.ExtraFiles = []*os.File{writer}
		return cmd
	}
}

func boundaryServiceFactory(marker string) func(string, ...string) *exec.Cmd {
	return func(name string, _ ...string) *exec.Cmd {
		role := "apache"
		if name == "/usr/bin/php" {
			role = "scheduler"
		}
		cmd := exec.Command(os.Args[0], "-test.run=^TestInitBoundaryFixture$", role)
		return cmd
	}
}

func boundaryReaper() *childReaper {
	r := newChildReaper()
	r.list = func() ([]os.DirEntry, error) { return nil, nil }
	return r
}

func appendBoundaryMarker(t *testing.T, path, value string) {
	t.Helper()
	f, err := os.OpenFile(path, os.O_CREATE|os.O_APPEND|os.O_WRONLY, 0600)
	if err != nil {
		t.Fatal(err)
	}
	if _, err := f.WriteString(value); err != nil {
		f.Close()
		t.Fatal(err)
	}
	if err := f.Close(); err != nil {
		t.Fatal(err)
	}
}

func readBoundaryLines(path string) []string {
	data, err := os.ReadFile(path)
	if err != nil {
		return nil
	}
	return strings.FieldsFunc(string(data), func(r rune) bool { return r == '\n' })
}

func waitBoundaryPrefix(t *testing.T, path, prefix string, timeout time.Duration) bool {
	t.Helper()
	deadline := time.Now().Add(timeout)
	for time.Now().Before(deadline) {
		if hasBoundaryLine(readBoundaryLines(path), prefix) {
			return true
		}
		time.Sleep(5 * time.Millisecond)
	}
	return hasBoundaryLine(readBoundaryLines(path), prefix)
}

func boundaryLine(lines []string, prefix string) string {
	for _, line := range lines {
		if strings.HasPrefix(line, prefix) {
			return line
		}
	}
	return ""
}

func boundaryID(line, key string) int {
	for _, field := range strings.Fields(line) {
		if value, ok := strings.CutPrefix(field, key); ok {
			var id int
			_, _ = fmt.Sscanf(value, "%d", &id)
			return id
		}
	}
	return 0
}

func hasBoundaryLine(lines []string, prefix string) bool {
	for _, line := range lines {
		if strings.HasPrefix(line, prefix) {
			return true
		}
	}
	return false
}
