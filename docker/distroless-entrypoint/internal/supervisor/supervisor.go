package supervisor

import (
	"context"
	"errors"
	"fmt"
	"os"
	"os/exec"
	"strconv"
	"strings"
	"sync"
	"syscall"
	"time"

	"snipeit-distroless-entrypoint/internal/handoff"
)

// Run supervises the one-shot init command and then its long-running service children.
func Run(ctx context.Context, initPath string, makeCommand func(string, ...string) *exec.Cmd, schedulerInterval, shutdownGrace time.Duration) error {
	return runWithInit(ctx, func(writer *os.File) *exec.Cmd {
		cmd := exec.Command(initPath)
		cmd.Env = os.Environ()
		cmd.Stdin, cmd.Stdout, cmd.Stderr = os.Stdin, os.Stdout, os.Stderr
		cmd.ExtraFiles = []*os.File{writer}
		return cmd
	}, makeCommand, schedulerInterval, shutdownGrace, newChildReaper())
}

// InitExitError retains the exact direct-child wait result from the init process.
type InitExitError struct {
	cause error
}

func (e *InitExitError) Error() string { return fmt.Sprintf("init exited: %v", e.cause) }
func (e *InitExitError) Unwrap() error { return e.cause }

// InitFailureExitCode returns the init process status for container exit propagation.
func InitFailureExitCode(err error) (int, bool) {
	var initErr *InitExitError
	if !errors.As(err, &initErr) {
		return 0, false
	}
	var exitErr *exec.ExitError
	if errors.As(initErr.cause, &exitErr) {
		if code := exitErr.ExitCode(); code >= 0 {
			return code, true
		}
		if status, ok := exitErr.Sys().(syscall.WaitStatus); ok && status.Signaled() {
			return 128 + int(status.Signal()), true
		}
	}
	return 1, true
}

func runWithInit(ctx context.Context, makeInit func(*os.File) *exec.Cmd, makeCommand func(string, ...string) *exec.Cmd, schedulerInterval, shutdownGrace time.Duration, reaper *childReaper) (resultErr error) {
	if makeInit == nil || makeCommand == nil || reaper == nil {
		return errors.New("invalid supervisor initialization configuration")
	}
	reaperCancel, reaperDone := startReaper(reaper)
	defer func() {
		stopReaper(reaperCancel, reaperDone)
	}()

	readEnd, writeEnd, err := os.Pipe()
	if err != nil {
		return fmt.Errorf("create init result pipe: %w", err)
	}
	init := makeInit(writeEnd)
	if init == nil {
		readEnd.Close()
		writeEnd.Close()
		return errors.New("init command factory returned nil")
	}
	if init.SysProcAttr == nil {
		init.SysProcAttr = &syscall.SysProcAttr{Setpgid: true}
	} else {
		attributes := *init.SysProcAttr
		attributes.Setpgid = true
		init.SysProcAttr = &attributes
	}
	if ctx.Err() != nil {
		readEnd.Close()
		writeEnd.Close()
		return nil
	}
	if err := reaper.start(init); err != nil {
		readEnd.Close()
		writeEnd.Close()
		return fmt.Errorf("start init: %w", err)
	}
	if err := writeEnd.Close(); err != nil {
		waitDone := make(chan error, 1)
		go func() { waitDone <- reaper.wait(init) }()
		stopProcessGroup(init, waitDone, shutdownGrace)
		readEnd.Close()
		return fmt.Errorf("close supervisor init pipe writer: %w", err)
	}

	type resultRead struct {
		value handoff.Result
		err   error
	}
	readDone := make(chan resultRead, 1)
	go func() {
		defer readEnd.Close()
		value, err := handoff.ReadResult(readEnd)
		readDone <- resultRead{value: value, err: err}
	}()
	waitDone := make(chan error, 1)
	go func() { waitDone <- reaper.wait(init) }()

	var initWaitErr error
	var initFinished bool
	var payload resultRead
	var payloadFinished bool
	for !initFinished || !payloadFinished {
		select {
		case <-ctx.Done():
			if !initFinished {
				stopProcessGroup(init, waitDone, shutdownGrace)
				initFinished = true
			}
			if !payloadFinished {
				payload = <-readDone
				payloadFinished = true
			}
			return nil
		case err := <-reaper.errors:
			if !initFinished {
				stopProcessGroup(init, waitDone, shutdownGrace)
			}
			if !payloadFinished {
				<-readDone
			}
			return err
		case err := <-waitDone:
			initWaitErr = err
			initFinished = true
		case read := <-readDone:
			payload = read
			payloadFinished = true
		}
	}
	if ctx.Err() != nil {
		return nil
	}
	if initWaitErr != nil {
		return &InitExitError{cause: initWaitErr}
	}
	if payload.err != nil {
		return fmt.Errorf("invalid init result: %w", payload.err)
	}
	if ctx.Err() != nil {
		return nil
	}
	switch payload.value.Disposition {
	case handoff.ExitSuccess:
		return nil
	case handoff.StartServices:
		childEnvironment := make([]string, 0, len(payload.value.Environment))
		for _, entry := range payload.value.Environment {
			childEnvironment = append(childEnvironment, entry.Name+"="+entry.Value)
		}
		withEnvironment := func(name string, args ...string) *exec.Cmd {
			cmd := makeCommand(name, args...)
			cmd.Env = childEnvironment
			return cmd
		}
		if ctx.Err() != nil {
			return nil
		}
		return superviseManaged(ctx, withEnvironment, schedulerInterval, shutdownGrace, reaper)
	default:
		return errors.New("invalid init result disposition")
	}
}

func startReaper(reaper *childReaper) (context.CancelFunc, <-chan error) {
	ctx, cancel := context.WithCancel(context.Background())
	done := make(chan error, 1)
	go func() { done <- reaper.run(ctx) }()
	return cancel, done
}

func stopReaper(cancel context.CancelFunc, done <-chan error) {
	cancel()
	if err := <-done; err != nil {
		fmt.Fprintln(os.Stderr, err)
	}
}

func superviseWith(ctx context.Context, makeCommand func(string, ...string) *exec.Cmd, schedulerInterval, shutdownGrace time.Duration) error {
	return superviseWithReaper(ctx, makeCommand, schedulerInterval, shutdownGrace, newChildReaper())
}

func superviseWithReaper(ctx context.Context, makeCommand func(string, ...string) *exec.Cmd, schedulerInterval, shutdownGrace time.Duration, reaper *childReaper) error {
	cancelReaper, reaperDone := startReaper(reaper)
	defer stopReaper(cancelReaper, reaperDone)
	return superviseManaged(ctx, makeCommand, schedulerInterval, shutdownGrace, reaper)
}

func superviseManaged(ctx context.Context, makeCommand func(string, ...string) *exec.Cmd, schedulerInterval, shutdownGrace time.Duration, reaper *childReaper) error {
	if ctx.Err() != nil {
		return nil
	}
	apache := makeCommand("/usr/sbin/apache2", "-DFOREGROUND")
	apache.SysProcAttr = &syscall.SysProcAttr{Setpgid: true}
	if err := reaper.start(apache); err != nil {
		return err
	}
	done := make(chan error, 1)
	go func() { done <- reaper.wait(apache) }()
	timer := time.NewTimer(schedulerInterval)
	timer.Stop()
	defer timer.Stop()
	schedulerDone := make(chan error, 1)
	var scheduler *exec.Cmd
	startScheduler := func() error {
		if ctx.Err() != nil {
			return context.Canceled
		}
		scheduler = makeCommand("/usr/bin/php", "artisan", "schedule:run")
		scheduler.SysProcAttr = &syscall.SysProcAttr{Setpgid: true}
		if err := reaper.start(scheduler); err != nil {
			return err
		}
		go func(cmd *exec.Cmd) { schedulerDone <- reaper.wait(cmd) }(scheduler)
		return nil
	}
	if err := startScheduler(); err != nil {
		stopProcessGroup(apache, done, shutdownGrace)
		if ctx.Err() != nil {
			return nil
		}
		return err
	}
	for {
		select {
		case err := <-reaper.errors:
			stopProcessGroup(apache, done, shutdownGrace)
			if scheduler != nil {
				stopProcessGroup(scheduler, schedulerDone, shutdownGrace)
			}
			return err
		case err := <-done:
			if scheduler != nil {
				stopProcessGroup(scheduler, schedulerDone, shutdownGrace)
			}
			if err == nil {
				return errors.New("apache exited unexpectedly")
			}
			return fmt.Errorf("apache exited: %w", err)
		case <-schedulerDone:
			scheduler = nil
			timer.Reset(schedulerInterval)
		case <-timer.C:
			if scheduler == nil {
				if err := startScheduler(); err != nil {
					stopProcessGroup(apache, done, shutdownGrace)
					if ctx.Err() != nil {
						return nil
					}
					return err
				}
			}
		case <-ctx.Done():
			var stopping sync.WaitGroup
			stopping.Add(1)
			go func() {
				defer stopping.Done()
				stopProcessGroup(apache, done, shutdownGrace)
			}()
			if scheduler != nil {
				stopping.Add(1)
				go func(cmd *exec.Cmd) {
					defer stopping.Done()
					stopProcessGroup(cmd, schedulerDone, shutdownGrace)
				}(scheduler)
			}
			stopping.Wait()
			return nil
		}
	}
}

func stopProcessGroup(cmd *exec.Cmd, done <-chan error, grace time.Duration) {
	if cmd == nil || cmd.Process == nil {
		return
	}
	_ = syscall.Kill(-cmd.Process.Pid, syscall.SIGTERM)
	timer := time.NewTimer(grace)
	defer timer.Stop()
	select {
	case <-done:
		return
	case <-timer.C:
		_ = syscall.Kill(-cmd.Process.Pid, syscall.SIGKILL)
		<-done
	}
}

// childReaper reserves direct children for exec.Cmd.Wait. It never waits for
// an arbitrary child: only exact unreserved adopted zombie PIDs are eligible.
type childReaper struct {
	mu       sync.Mutex
	reserved map[int]*exec.Cmd
	errors   chan error
	list     func() ([]os.DirEntry, error)
	read     func(int) ([]byte, error)
	collect  func(int) error
}

type childIdentity struct {
	state  string
	parent int
	start  string
}

func parseChildIdentity(stat []byte) (childIdentity, error) {
	end := strings.LastIndexByte(string(stat), ')')
	if end < 0 {
		return childIdentity{}, errors.New("invalid process stat")
	}
	fields := strings.Fields(string(stat[end+1:]))
	if len(fields) < 20 {
		return childIdentity{}, errors.New("short process stat")
	}
	parent, err := strconv.Atoi(fields[1])
	if err != nil {
		return childIdentity{}, err
	}
	return childIdentity{fields[0], parent, fields[19]}, nil
}

func newChildReaper() *childReaper {
	return &childReaper{
		reserved: make(map[int]*exec.Cmd), errors: make(chan error, 1),
		list: func() ([]os.DirEntry, error) { return os.ReadDir("/proc") },
		read: func(pid int) ([]byte, error) { return os.ReadFile(fmt.Sprintf("/proc/%d/stat", pid)) },
		collect: func(pid int) error {
			var status syscall.WaitStatus
			_, err := syscall.Wait4(pid, &status, syscall.WNOHANG, nil)
			return err
		},
	}
}

func (r *childReaper) start(cmd *exec.Cmd) error {
	r.mu.Lock()
	defer r.mu.Unlock()
	if err := cmd.Start(); err != nil {
		return err
	}
	r.reserved[cmd.Process.Pid] = cmd
	return nil
}

func (r *childReaper) wait(cmd *exec.Cmd) error {
	// Only Cmd.Wait owns this direct child's exit status and pipe cleanup.
	err := cmd.Wait()
	r.release(cmd)
	return err
}

func (r *childReaper) release(cmd *exec.Cmd) {
	r.mu.Lock()
	defer r.mu.Unlock()
	// A completed wait may race a new launch that reused this numeric PID.
	// Release only the original command's reservation, never a new owner's.
	if r.reserved[cmd.Process.Pid] == cmd {
		delete(r.reserved, cmd.Process.Pid)
	}
}

func (r *childReaper) scan() error {
	r.mu.Lock()
	defer r.mu.Unlock()
	entries, err := r.list()
	if err != nil {
		return fmt.Errorf("enumerate adopted children: %w", err)
	}
	for _, entry := range entries {
		pid, err := strconv.Atoi(entry.Name())
		if err != nil || pid <= 0 || r.reserved[pid] != nil {
			continue
		}
		data, err := r.read(pid)
		if err != nil {
			continue
		}
		observed, err := parseChildIdentity(data)
		if err != nil || observed.state != "Z" || observed.parent != os.Getpid() {
			continue
		}
		for {
			// Revalidate stale scan identity, parent and state before every wait.
			currentData, err := r.read(pid)
			if err != nil {
				break
			}
			current, err := parseChildIdentity(currentData)
			if err != nil || current != observed || r.reserved[pid] != nil {
				break
			}
			err = r.collect(pid)
			if errors.Is(err, syscall.EINTR) {
				continue
			}
			if err != nil && !errors.Is(err, syscall.ECHILD) {
				return fmt.Errorf("reap adopted child %d: %w", pid, err)
			}
			break
		}
	}
	return nil
}

func (r *childReaper) run(ctx context.Context) error {
	ticker := time.NewTicker(100 * time.Millisecond)
	defer ticker.Stop()
	for {
		if err := r.scan(); err != nil {
			r.errors <- err
			// Stay joinable until supervisor completes its established shutdown path.
			<-ctx.Done()
			return err
		}
		select {
		case <-ctx.Done():
			return r.scan()
		case <-ticker.C:
		}
	}
}
