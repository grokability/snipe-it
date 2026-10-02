package main

import (
	"errors"
	"fmt"
	"os"
	"strings"
	"syscall"

	"snipeit-distroless-entrypoint/internal/handoff"
	"snipeit-distroless-entrypoint/internal/startup"
)

const resultFD = 3

func main() {
	if err := runInitResult(resultFD, initialize); err != nil {
		fmt.Fprintln(os.Stderr, err)
		os.Exit(1)
	}
}

func initialize() (handoff.Result, error) {
	startServices, err := startup.Initialize(startup.Command)
	if err != nil {
		return handoff.Result{}, err
	}
	if !startServices {
		return handoff.Result{Disposition: handoff.ExitSuccess}, nil
	}
	environment, err := currentEnvironment()
	if err != nil {
		return handoff.Result{}, err
	}
	return handoff.Result{Disposition: handoff.StartServices, Environment: environment}, nil
}

func currentEnvironment() ([]handoff.EnvironmentEntry, error) {
	values := os.Environ()
	entries := make([]handoff.EnvironmentEntry, 0, len(values))
	for _, value := range values {
		name, content, ok := strings.Cut(value, "=")
		if !ok || name == "" {
			return nil, errors.New("invalid process environment entry")
		}
		entries = append(entries, handoff.EnvironmentEntry{Name: name, Value: content})
	}
	return entries, nil
}

// runInitResult marks the inherited descriptor close-on-exec before startup can
// launch PHP/Artisan children. A frame is emitted only for successful init.
func runInitResult(fd int, initialize func() (handoff.Result, error)) error {
	if fd < 0 || initialize == nil {
		return errors.New("invalid init result channel")
	}
	writer := os.NewFile(uintptr(fd), "init-result")
	if writer == nil {
		return errors.New("init result descriptor is unavailable")
	}
	defer writer.Close()

	syscall.CloseOnExec(fd)
	flags, _, errno := syscall.Syscall(syscall.SYS_FCNTL, uintptr(fd), syscall.F_GETFD, 0)
	if errno != 0 {
		return fmt.Errorf("inspect init result descriptor: %w", errno)
	}
	if flags&syscall.FD_CLOEXEC == 0 {
		return errors.New("init result descriptor is not close-on-exec")
	}

	result, err := initialize()
	if err != nil {
		return err
	}
	if err := handoff.WriteResult(writer, result); err != nil {
		return fmt.Errorf("write init result: %w", err)
	}
	return nil
}
