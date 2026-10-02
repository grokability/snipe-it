package main

import (
	"errors"
	"os"
	"os/exec"
	"strings"
	"syscall"
	"testing"

	"snipeit-distroless-entrypoint/internal/handoff"
)

const initResultFD = 3

func TestInitResultProcessFixture(t *testing.T) {
	if os.Getenv("INIT_RESULT_FIXTURE") != "1" {
		return
	}
	err := runInitResult(initResultFD, func() (handoff.Result, error) {
		if os.Getenv("INIT_RESULT_FAILURE") == "1" {
			return handoff.Result{}, errors.New("fixture initialization failed")
		}
		flags, _, errno := syscall.Syscall(syscall.SYS_FCNTL, initResultFD, syscall.F_GETFD, 0)
		if errno != 0 {
			return handoff.Result{}, errno
		}
		if flags&syscall.FD_CLOEXEC == 0 {
			return handoff.Result{}, syscall.EINVAL
		}
		cmd := exec.Command(os.Args[0], "-test.run=^TestInitResultDescendantFixture$")
		cmd.Env = append(os.Environ(), "INIT_RESULT_DESCENDANT=1")
		if output, err := cmd.CombinedOutput(); err != nil {
			return handoff.Result{}, &fixtureError{output: string(output), err: err}
		}
		if os.Getenv("INIT_RESULT_EXIT_SUCCESS") == "1" {
			return handoff.Result{Disposition: handoff.ExitSuccess}, nil
		}
		return handoff.Result{
			Disposition: handoff.StartServices,
			Environment: []handoff.EnvironmentEntry{
				{Name: "DB_PASSWORD", Value: "line one\nquote='\";$(literal)"},
				{Name: "EMPTY_VALUE", Value: ""},
			},
		}, nil
	})
	if err != nil {
		t.Fatal(err)
	}
}

type fixtureError struct {
	output string
	err    error
}

func (e *fixtureError) Error() string { return e.output + ": " + e.err.Error() }

func TestInitResultDescendantFixture(t *testing.T) {
	if os.Getenv("INIT_RESULT_DESCENDANT") != "1" {
		return
	}
	_, _, errno := syscall.Syscall(syscall.SYS_FCNTL, initResultFD, syscall.F_GETFD, 0)
	if errno != syscall.EBADF {
		t.Fatalf("handoff fd %d was inherited by init descendant: errno=%v", initResultFD, errno)
	}
}

func TestInitWritesOneResultAfterSuccessfulInitializationAndClosesPipe(t *testing.T) {
	result, waitErr, readErr := runInitFixture(t, nil)
	if waitErr != nil {
		t.Fatalf("init fixture exit = %v", waitErr)
	}
	if readErr != nil {
		t.Fatalf("read init result: %v", readErr)
	}
	if result.Disposition != handoff.StartServices {
		t.Fatalf("disposition = %v, want start-services", result.Disposition)
	}
	if len(result.Environment) != 2 || result.Environment[0].Name != "DB_PASSWORD" || result.Environment[0].Value != "line one\nquote='\";$(literal)" || result.Environment[1].Name != "EMPTY_VALUE" || result.Environment[1].Value != "" {
		t.Fatalf("environment was not preserved exactly: %#v", result.Environment)
	}
	if strings.Contains(result.Environment[0].Value, "\\n") {
		t.Fatal("newline was encoded as textual escape instead of preserved data")
	}
}

func TestInitExitSuccessWritesNoEnvironment(t *testing.T) {
	result, waitErr, readErr := runInitFixture(t, []string{"INIT_RESULT_EXIT_SUCCESS=1"})
	if waitErr != nil || readErr != nil {
		t.Fatalf("exit-success result: child=%v read=%v", waitErr, readErr)
	}
	if result.Disposition != handoff.ExitSuccess || len(result.Environment) != 0 {
		t.Fatalf("result = %#v, want exit-success with no environment", result)
	}
}

func TestInitFailureExitsNonzeroWithoutSuccessFrame(t *testing.T) {
	_, waitErr, readErr := runInitFixture(t, []string{"INIT_RESULT_FAILURE=1"})
	if waitErr == nil {
		t.Fatal("init failure exited successfully")
	}
	if readErr == nil {
		t.Fatal("failed initialization wrote a successful result frame")
	}
}

func runInitFixture(t *testing.T, extraEnv []string) (handoff.Result, error, error) {
	t.Helper()
	readEnd, writeEnd, err := os.Pipe()
	if err != nil {
		t.Fatal(err)
	}
	cmd := exec.Command(os.Args[0], "-test.run=^TestInitResultProcessFixture$")
	cmd.ExtraFiles = []*os.File{writeEnd}
	cmd.Env = append(os.Environ(), "INIT_RESULT_FIXTURE=1")
	cmd.Env = append(cmd.Env, extraEnv...)
	if err := cmd.Start(); err != nil {
		readEnd.Close()
		writeEnd.Close()
		t.Fatal(err)
	}
	if err := writeEnd.Close(); err != nil {
		t.Fatal(err)
	}
	result, readErr := handoff.ReadResult(readEnd)
	readEnd.Close()
	waitErr := cmd.Wait()
	return result, waitErr, readErr
}

func TestCurrentEnvironmentPreservesOpaqueValues(t *testing.T) {
	want := "line one\nline two; quote='\";$(literal)=value ☃"
	t.Setenv("INIT_ENV_BOUNDARY_TEST", want)
	entries, err := currentEnvironment()
	if err != nil {
		t.Fatal(err)
	}
	for _, entry := range entries {
		if entry.Name == "INIT_ENV_BOUNDARY_TEST" {
			if entry.Value != want {
				t.Fatalf("environment value = %q, want exact value %q", entry.Value, want)
			}
			return
		}
	}
	t.Fatal("test environment entry missing from handoff environment")
}
