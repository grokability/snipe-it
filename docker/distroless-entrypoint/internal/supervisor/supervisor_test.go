package supervisor

import (
	"context"
	"errors"
	"fmt"
	"os"
	"os/exec"
	"os/signal"
	"path/filepath"
	"strconv"
	"strings"
	"sync"
	"syscall"
	"testing"
	"time"
)

func TestSupervisorChildFixture(t *testing.T) {
	mode := os.Getenv("SUPERVISOR_FIXTURE_MODE")
	if mode == "" {
		return
	}
	marker := os.Getenv("SUPERVISOR_FIXTURE_MARKER")
	appendFixtureMarker(t, marker, fmt.Sprintf("%s %d\n", os.Getenv("SUPERVISOR_FIXTURE_ROLE"), time.Now().UnixNano()))
	if mode == "exit" {
		os.Exit(0)
	}
	if mode == "exit-error" {
		os.Exit(1)
	}
	if mode == "exit-error-delayed" {
		time.Sleep(100 * time.Millisecond)
		os.Exit(1)
	}
	if mode == "ignore-term" {
		signal.Ignore(syscall.SIGTERM, syscall.SIGINT)
		for {
			time.Sleep(time.Hour)
		}
	}
	signals := make(chan os.Signal, 1)
	signal.Notify(signals, syscall.SIGTERM, syscall.SIGINT)
	<-signals
	appendFixtureMarker(t, marker, fmt.Sprintf("%s signal %d\n", os.Getenv("SUPERVISOR_FIXTURE_ROLE"), time.Now().UnixNano()))
}

func TestSupervisorRetriesSchedulerAfterUpstreamIntervalAndStopsCleanly(t *testing.T) {
	marker := filepath.Join(t.TempDir(), "events")
	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()
	interval := 150 * time.Millisecond
	done := make(chan error, 1)
	go func() {
		done <- superviseWith(ctx, fixtureCommandFactory(marker), interval, time.Second)
	}()
	if !waitForFixturePrefix(t, marker, "scheduler ", 2, 2*time.Second) {
		cancel()
		select {
		case <-done:
		case <-time.After(time.Second):
		}
		t.Fatal("scheduler did not run twice")
	}
	events := readFixtureLines(t, marker)
	var starts []int64
	for _, line := range events {
		fields := strings.Fields(line)
		if len(fields) == 2 && fields[0] == "scheduler" {
			stamp, err := strconv.ParseInt(fields[1], 10, 64)
			if err != nil {
				t.Fatal(err)
			}
			starts = append(starts, stamp)
		}
	}
	if len(starts) < 2 {
		t.Fatalf("scheduler starts = %d, want at least 2; events: %v", len(starts), events)
	}
	if elapsed := time.Duration(starts[1] - starts[0]); elapsed < interval-25*time.Millisecond {
		t.Fatalf("scheduler retry interval = %s, want at least %s", elapsed, interval-25*time.Millisecond)
	}
	cancel()
	select {
	case err := <-done:
		if err != nil {
			t.Fatalf("supervisor shutdown = %v, want clean status", err)
		}
	case <-time.After(2 * time.Second):
		t.Fatal("supervisor did not shut down after cancellation")
	}
	if !containsFixtureLine(readFixtureLines(t, marker), "apache signal") {
		t.Fatal("Apache process did not receive the termination signal")
	}
}

func TestSupervisorSignalsManagedProcessGroupsAndReapsCommands(t *testing.T) {
	marker := filepath.Join(t.TempDir(), "events")
	ctx, cancel := context.WithCancel(context.Background())
	done := make(chan error, 1)
	go func() {
		done <- superviseWith(ctx, func(name string, args ...string) *exec.Cmd {
			role := "apache"
			if name == "/usr/bin/php" {
				role = "scheduler"
			}
			return fixtureCommand(marker, role, "wait")
		}, time.Hour, time.Second)
	}()
	if !waitForFixturePrefix(t, marker, "apache ", 1, time.Second) || !waitForFixturePrefix(t, marker, "scheduler ", 1, time.Second) {
		cancel()
		select {
		case <-done:
		case <-time.After(time.Second):
		}
		t.Fatal("managed commands did not start")
	}
	cancel()
	select {
	case err := <-done:
		if err != nil {
			t.Fatalf("supervisor shutdown = %v, want clean status", err)
		}
	case <-time.After(2 * time.Second):
		t.Fatal("supervisor did not reap managed commands")
	}
	lines := readFixtureLines(t, marker)
	if !containsFixtureLine(lines, "apache signal") || !containsFixtureLine(lines, "scheduler signal") {
		t.Fatalf("termination was not delivered to both managed groups: %v", lines)
	}
}

func TestSupervisorPropagatesApacheFatalExitAndStopsScheduler(t *testing.T) {
	marker := filepath.Join(t.TempDir(), "events")
	done := make(chan error, 1)
	go func() {
		done <- superviseWith(context.Background(), func(name string, args ...string) *exec.Cmd {
			if name == "/usr/bin/php" {
				return fixtureCommand(marker, "scheduler", "wait")
			}
			return fixtureCommand(marker, "apache", "exit-error-delayed")
		}, time.Hour, time.Second)
	}()
	select {
	case err := <-done:
		if err == nil || !strings.Contains(err.Error(), "apache exited") {
			t.Fatalf("supervisor result = %v, want Apache fatal exit error", err)
		}
	case <-time.After(2 * time.Second):
		t.Fatal("supervisor did not report Apache fatal exit")
	}
	if !containsFixtureLine(readFixtureLines(t, marker), "scheduler signal") {
		t.Fatal("scheduler process group was not stopped after Apache exited")
	}
}

func TestSupervisorEscalatesWhenManagedProcessIgnoresTermination(t *testing.T) {
	marker := filepath.Join(t.TempDir(), "events")
	commands := make(chan *exec.Cmd, 2)
	ctx, cancel := context.WithCancel(context.Background())
	done := make(chan error, 1)
	go func() {
		done <- superviseWith(ctx, func(name string, args ...string) *exec.Cmd {
			role := "apache"
			if name == "/usr/bin/php" {
				role = "scheduler"
			}
			cmd := fixtureCommand(marker, role, "ignore-term")
			commands <- cmd
			return cmd
		}, time.Hour, 100*time.Millisecond)
	}()
	var apache, scheduler *exec.Cmd
	select {
	case apache = <-commands:
	case <-time.After(time.Second):
		t.Fatal("Apache fixture did not start")
	}
	select {
	case scheduler = <-commands:
	case <-time.After(time.Second):
		cancel()
		syscall.Kill(-apache.Process.Pid, syscall.SIGKILL)
		t.Fatal("scheduler fixture did not start")
	}
	if !waitForFixturePrefix(t, marker, "apache ", 1, 5*time.Second) || !waitForFixturePrefix(t, marker, "scheduler ", 1, 5*time.Second) {
		cancel()
		syscall.Kill(-apache.Process.Pid, syscall.SIGKILL)
		syscall.Kill(-scheduler.Process.Pid, syscall.SIGKILL)
		select {
		case err := <-done:
			t.Fatalf("managed fixtures did not initialize; events=%v; supervisor=%v", readFixtureLines(t, marker), err)
		case <-time.After(time.Second):
			t.Fatalf("managed fixtures did not initialize; events=%v", readFixtureLines(t, marker))
		}
	}
	cancel()
	select {
	case err := <-done:
		if err != nil {
			t.Fatalf("shutdown result = %v, want clean status after escalation", err)
		}
	case <-time.After(500 * time.Millisecond):
		syscall.Kill(-apache.Process.Pid, syscall.SIGKILL)
		syscall.Kill(-scheduler.Process.Pid, syscall.SIGKILL)
		<-done
		t.Fatal("supervisor waited indefinitely for processes that ignored SIGTERM")
	}
}

func fixtureCommandFactory(marker string) func(string, ...string) *exec.Cmd {
	return func(name string, args ...string) *exec.Cmd {
		role, mode := "apache", "wait"
		if name == "/usr/bin/php" {
			role, mode = "scheduler", "exit"
		}
		return fixtureCommand(marker, role, mode)
	}
}

func fixtureCommand(marker, role, mode string) *exec.Cmd {
	cmd := exec.Command(os.Args[0], "-test.run=^TestSupervisorChildFixture$")
	cmd.Env = append(os.Environ(), "SUPERVISOR_FIXTURE_ROLE="+role, "SUPERVISOR_FIXTURE_MODE="+mode, "SUPERVISOR_FIXTURE_MARKER="+marker)
	return cmd
}

func appendFixtureMarker(t *testing.T, path, value string) {
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

func readFixtureLines(t *testing.T, path string) []string {
	t.Helper()
	data, err := os.ReadFile(path)
	if err != nil {
		return nil
	}
	return strings.FieldsFunc(string(data), func(r rune) bool { return r == '\n' })
}

func waitForFixtureLines(t *testing.T, path string, count int, timeout time.Duration) bool {
	t.Helper()
	deadline := time.Now().Add(timeout)
	for time.Now().Before(deadline) {
		if len(readFixtureLines(t, path)) >= count {
			return true
		}
		time.Sleep(5 * time.Millisecond)
	}
	return len(readFixtureLines(t, path)) >= count
}

func waitForFixturePrefix(t *testing.T, path, prefix string, count int, timeout time.Duration) bool {
	t.Helper()
	deadline := time.Now().Add(timeout)
	for time.Now().Before(deadline) {
		found := 0
		for _, line := range readFixtureLines(t, path) {
			if strings.HasPrefix(line, prefix) {
				found++
			}
		}
		if found >= count {
			return true
		}
		time.Sleep(5 * time.Millisecond)
	}
	return false
}

func containsFixtureLine(lines []string, prefix string) bool {
	for _, line := range lines {
		if strings.HasPrefix(line, prefix) {
			return true
		}
	}
	return false
}

// Run as PID 1 in the final runtime; external probes create real adopted children.
func TestPIDOneSupervisorFixture(t *testing.T) {
	if os.Getenv("PID_ONE_SUPERVISOR_FIXTURE") != "1" {
		t.Skip("isolated PID1 fixture only")
	}
	if os.Getpid() != 1 {
		t.Fatal("fixture must be PID 1")
	}
	ctx, stop := signal.NotifyContext(context.Background(), syscall.SIGTERM, syscall.SIGINT)
	defer stop()
	marker := "/tmp/reaper-supervision-events"
	if err := superviseWith(ctx, func(name string, args ...string) *exec.Cmd {
		role := "apache"
		if name == "/usr/bin/php" {
			role = "scheduler"
		}
		mode := "wait"
		if os.Getenv("REAPER_IGNORE_TERM") == "1" {
			mode = "ignore-term"
		}
		return fixtureCommand(marker, role, mode)
	}, time.Hour, time.Second); err != nil {
		t.Fatal(err)
	}
}

func TestReaperStatusFixture(t *testing.T) {
	if value := os.Getenv("REAPER_STATUS_FIXTURE"); value != "" {
		code, _ := strconv.Atoi(value)
		os.Exit(code)
	}
}

func TestReaperOwnsOnlyUnreservedStatusesUnderHighFrequencyLaunches(t *testing.T) {
	r := newChildReaper()
	ctx, cancel := context.WithCancel(context.Background())
	finished := make(chan error, 1)
	go func() { finished <- r.run(ctx) }()
	defer func() {
		cancel()
		if err := <-finished; err != nil {
			t.Error(err)
		}
	}()
	runReaperOwnershipStress(t, r)
}

func runReaperOwnershipStress(t *testing.T, r *childReaper) {
	executable, err := os.Executable()
	if err != nil {
		t.Fatal(err)
	}
	var workers sync.WaitGroup
	var seenMu sync.Mutex
	seen := map[int]bool{}
	reused := 0
	for worker := 0; worker < 16; worker++ {
		workers.Add(1)
		go func(worker int) {
			defer workers.Done()
			for i := 0; i < 32; i++ {
				code := (worker + i) % 37
				cmd := exec.Command(executable, "-test.run=^TestReaperStatusFixture$")
				cmd.Env = append(os.Environ(), "REAPER_STATUS_FIXTURE="+strconv.Itoa(code))
				if err := r.start(cmd); err != nil {
					t.Error(err)
					return
				}
				pid := cmd.Process.Pid
				seenMu.Lock()
				if seen[pid] {
					reused++
				}
				seen[pid] = true
				seenMu.Unlock()
				err := r.wait(cmd)
				if cmd.ProcessState == nil || cmd.ProcessState.Pid() != pid || cmd.ProcessState.ExitCode() != code {
					t.Errorf("pid %d expected exit %d, state=%v error=%v", pid, code, cmd.ProcessState, err)
				}
				if (code == 0) != (err == nil) {
					t.Errorf("pid %d error=%v for code %d", pid, err, code)
				}
			}
		}(worker)
	}
	workers.Wait()
	bad := exec.Command("/no-such-reaper-client")
	if err := r.start(bad); err == nil {
		t.Fatal("missing executable launched")
	}
	r.mu.Lock()
	remaining := len(r.reserved)
	r.mu.Unlock()
	if remaining != 0 {
		t.Fatalf("leaked %d reservations", remaining)
	}
	t.Logf("512 exact direct wait results verified; observed numeric PID reuse=%d", reused)
}

func reaperStat(state, parent, start string) []byte {
	return []byte("23 (fixture ) with spaces) " + state + " " + parent + " " + strings.Repeat("0 ", 17) + start + " 0")
}

func TestReaperParserAndStalePIDTransitions(t *testing.T) {
	id, err := parseChildIdentity(reaperStat("Z", "1", "123"))
	if err != nil || id.state != "Z" || id.parent != 1 || id.start != "123" {
		t.Fatalf("identity=%+v err=%v", id, err)
	}
	for _, data := range []string{"", "23 (bad) Z", "23 no close"} {
		if _, err := parseChildIdentity([]byte(data)); err == nil {
			t.Fatalf("accepted %q", data)
		}
	}
	root := t.TempDir()
	if err := os.Mkdir(filepath.Join(root, "23"), 0755); err != nil {
		t.Fatal(err)
	}
	for _, mode := range []string{"reserved", "reused", "gone", "live", "foreign", "eintr", "echild", "fatal", "collect"} {
		t.Run(mode, func(t *testing.T) {
			r := newChildReaper()
			reads, waits := 0, 0
			r.list = func() ([]os.DirEntry, error) { return os.ReadDir(root) }
			if mode == "reserved" {
				r.reserved[23] = &exec.Cmd{}
			}
			r.read = func(pid int) ([]byte, error) {
				reads++
				start, parent, state := "123", strconv.Itoa(os.Getpid()), "Z"
				if reads > 1 {
					switch mode {
					case "reused":
						start = "456"
					case "gone":
						return nil, os.ErrNotExist
					case "live":
						state = "S"
					case "foreign":
						parent = "999999"
					}
				}
				return reaperStat(state, parent, start), nil
			}
			r.collect = func(pid int) error {
				if pid != 23 {
					t.Fatalf("broad/wrong wait pid=%d", pid)
				}
				waits++
				if mode == "eintr" && waits == 1 {
					return syscall.EINTR
				}
				if mode == "echild" {
					return syscall.ECHILD
				}
				if mode == "fatal" {
					return syscall.EINVAL
				}
				return nil
			}
			err := r.scan()
			want := 1
			if mode == "reserved" || mode == "reused" || mode == "gone" || mode == "live" || mode == "foreign" {
				want = 0
			}
			if mode == "eintr" {
				want = 2
			}
			if waits != want || (err != nil) != (mode == "fatal") {
				t.Fatalf("waits=%d want=%d err=%v", waits, want, err)
			}
		})
	}
}

func TestReaperEnumerationFailureIsObservableAndJoinable(t *testing.T) {
	r := newChildReaper()
	r.list = func() ([]os.DirEntry, error) { return nil, os.ErrPermission }
	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()
	finished := make(chan error, 1)
	go func() { finished <- r.run(ctx) }()
	select {
	case err := <-r.errors:
		if !errors.Is(err, os.ErrPermission) {
			t.Fatal(err)
		}
	case <-time.After(time.Second):
		t.Fatal("failure not observable")
	}
	cancel()
	select {
	case err := <-finished:
		if !errors.Is(err, os.ErrPermission) {
			t.Fatal(err)
		}
	case <-time.After(time.Second):
		t.Fatal("reaper did not join")
	}
}

func TestReaperReservationReleaseDoesNotRemoveReusedPIDOwner(t *testing.T) {
	r := newChildReaper()
	old := &exec.Cmd{Process: &os.Process{Pid: 23}}
	replacement := &exec.Cmd{Process: &os.Process{Pid: 23}}
	r.reserved[23] = replacement
	r.release(old)
	if r.reserved[23] != replacement {
		t.Fatal("old wait removed reused PID reservation")
	}
	r.release(replacement)
	if len(r.reserved) != 0 {
		t.Fatal("current owner reservation leaked")
	}
}

func TestSupervisorReaperFailureStopsManagedGroups(t *testing.T) {
	marker := filepath.Join(t.TempDir(), "events")
	r := newChildReaper()
	r.list = func() ([]os.DirEntry, error) {
		if containsFixtureLine(readFixtureLines(t, marker), "scheduler ") {
			return nil, os.ErrPermission
		}
		return os.ReadDir("/proc")
	}
	err := superviseWithReaper(context.Background(), func(name string, args ...string) *exec.Cmd {
		role := "apache"
		if name == "/usr/bin/php" {
			role = "scheduler"
		}
		return fixtureCommand(marker, role, "wait")
	}, time.Hour, time.Second, r)
	if !errors.Is(err, os.ErrPermission) {
		t.Fatalf("error=%v", err)
	}
	lines := readFixtureLines(t, marker)
	if !containsFixtureLine(lines, "apache signal") || !containsFixtureLine(lines, "scheduler signal") {
		t.Fatalf("shutdown markers=%v", lines)
	}
}

func TestPIDOneOwnershipRaceFixture(t *testing.T) {
	if os.Getenv("PID_ONE_SUPERVISOR_FIXTURE") != "1" {
		t.Skip("PID1 fixture only")
	}
	if os.Getpid() != 1 {
		t.Fatal("not PID1")
	}
	r := newChildReaper()
	ctx, cancel := context.WithCancel(context.Background())
	done := make(chan error, 1)
	go func() { done <- r.run(ctx) }()
	defer func() {
		cancel()
		if err := <-done; err != nil {
			t.Error(err)
		}
	}()
	if err := os.WriteFile("/tmp/ownership-ready", []byte("ready"), 0600); err != nil {
		t.Fatal(err)
	}
	runReaperOwnershipStress(t, r)
	if err := os.WriteFile("/tmp/ownership-finished", []byte("512 statuses verified"), 0600); err != nil {
		t.Fatal(err)
	}
	deadline := time.Now().Add(60 * time.Second)
	for time.Now().Before(deadline) {
		if _, err := os.Stat("/tmp/ownership-release"); err == nil {
			return
		}
		time.Sleep(10 * time.Millisecond)
	}
	t.Fatal("external orphan verification did not release fixture")
}
