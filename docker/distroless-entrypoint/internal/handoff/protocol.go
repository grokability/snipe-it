package handoff

import (
	"bytes"
	"encoding/binary"
	"errors"
	"fmt"
	"io"
	"math"
	"strings"
)

const (
	// MaxPayloadBytes bounds memory use while reading a result from the init child.
	MaxPayloadBytes            = 8 << 20
	maxEnvironmentEntries      = 1 << 16
	protocolVersion       byte = 1
	headerSize                 = 9
)

var frameMagic = [4]byte{'S', 'I', 'T', 'P'}

// Disposition tells the supervisor whether to start long-running services.
type Disposition byte

const (
	StartServices Disposition = 1
	ExitSuccess   Disposition = 2
)

// EnvironmentEntry preserves one operating-system environment name/value pair.
// Values are opaque bytes held in Go strings; neither field is line parsed.
type EnvironmentEntry struct {
	Name  string
	Value string
}

// Result is the successful application initialization result sent to PID 1.
type Result struct {
	Disposition Disposition
	Environment []EnvironmentEntry
}

// WriteResult writes one versioned, length-delimited result frame.
func WriteResult(w io.Writer, result Result) error {
	if err := validateResult(result); err != nil {
		return err
	}

	bodySize, err := resultBodySize(result)
	if err != nil {
		return err
	}
	if bodySize > MaxPayloadBytes {
		return fmt.Errorf("init result payload exceeds %d bytes", MaxPayloadBytes)
	}

	var body bytes.Buffer
	body.Grow(int(bodySize))
	body.WriteByte(byte(result.Disposition))
	if err := writeUint32(&body, uint32(len(result.Environment))); err != nil {
		return err
	}
	for _, entry := range result.Environment {
		if err := writeField(&body, entry.Name); err != nil {
			return err
		}
		if err := writeField(&body, entry.Value); err != nil {
			return err
		}
	}

	var header [headerSize]byte
	copy(header[:4], frameMagic[:])
	header[4] = protocolVersion
	binary.BigEndian.PutUint32(header[5:], uint32(body.Len()))
	if err := writeAll(w, header[:]); err != nil {
		return fmt.Errorf("write init result header: %w", err)
	}
	if err := writeAll(w, body.Bytes()); err != nil {
		return fmt.Errorf("write init result payload: %w", err)
	}
	return nil
}

// ReadResult reads exactly one complete frame and requires EOF afterward.
func ReadResult(r io.Reader) (Result, error) {
	var header [headerSize]byte
	if _, err := io.ReadFull(r, header[:]); err != nil {
		return Result{}, fmt.Errorf("read init result header: %w", err)
	}
	if !bytes.Equal(header[:4], frameMagic[:]) {
		return Result{}, errors.New("invalid init result magic")
	}
	if header[4] != protocolVersion {
		return Result{}, fmt.Errorf("unsupported init result version %d", header[4])
	}
	bodySize := binary.BigEndian.Uint32(header[5:])
	if bodySize > MaxPayloadBytes {
		return Result{}, fmt.Errorf("init result payload exceeds %d bytes", MaxPayloadBytes)
	}
	if bodySize < 5 {
		return Result{}, errors.New("init result payload is too short")
	}
	body := make([]byte, int(bodySize))
	if _, err := io.ReadFull(r, body); err != nil {
		return Result{}, fmt.Errorf("read init result payload: %w", err)
	}
	var trailing [1]byte
	if _, err := io.ReadFull(r, trailing[:]); err == nil {
		return Result{}, errors.New("trailing bytes after init result")
	} else if !errors.Is(err, io.EOF) {
		return Result{}, fmt.Errorf("check init result EOF: %w", err)
	}
	return parseBody(body)
}

func parseBody(body []byte) (Result, error) {
	r := bytes.NewReader(body)
	action, err := r.ReadByte()
	if err != nil {
		return Result{}, fmt.Errorf("read init result disposition: %w", err)
	}
	count, err := readUint32(r)
	if err != nil {
		return Result{}, fmt.Errorf("read init result environment count: %w", err)
	}
	if count > maxEnvironmentEntries || uint64(count) > uint64(r.Len()/8) {
		return Result{}, errors.New("invalid init result environment count")
	}

	result := Result{Disposition: Disposition(action), Environment: make([]EnvironmentEntry, 0, int(count))}
	for i := uint32(0); i < count; i++ {
		name, err := readField(r)
		if err != nil {
			return Result{}, fmt.Errorf("read init result environment name: %w", err)
		}
		value, err := readField(r)
		if err != nil {
			return Result{}, fmt.Errorf("read init result environment value: %w", err)
		}
		result.Environment = append(result.Environment, EnvironmentEntry{Name: name, Value: value})
	}
	if r.Len() != 0 {
		return Result{}, errors.New("trailing data in init result payload")
	}
	if err := validateResult(result); err != nil {
		return Result{}, err
	}
	return result, nil
}

func validateResult(result Result) error {
	switch result.Disposition {
	case StartServices:
	case ExitSuccess:
		if len(result.Environment) != 0 {
			return errors.New("exit-success init result cannot include environment")
		}
	default:
		return fmt.Errorf("invalid init result disposition %d", result.Disposition)
	}
	if len(result.Environment) > maxEnvironmentEntries {
		return errors.New("too many init result environment entries")
	}
	for _, entry := range result.Environment {
		if entry.Name == "" || strings.ContainsAny(entry.Name, "=\x00") {
			return errors.New("invalid init result environment name")
		}
		if strings.IndexByte(entry.Value, 0) >= 0 {
			return errors.New("invalid NUL in init result environment value")
		}
	}
	return nil
}

func resultBodySize(result Result) (uint64, error) {
	size := uint64(5) // disposition and environment count
	for _, entry := range result.Environment {
		if uint64(len(entry.Name)) > math.MaxUint32 || uint64(len(entry.Value)) > math.MaxUint32 {
			return 0, errors.New("init result environment entry is too large")
		}
		size += 8 + uint64(len(entry.Name)) + uint64(len(entry.Value))
		if size > MaxPayloadBytes {
			return size, nil
		}
	}
	return size, nil
}

func writeField(w io.Writer, value string) error {
	if err := writeUint32(w, uint32(len(value))); err != nil {
		return err
	}
	return writeAll(w, []byte(value))
}

func readField(r *bytes.Reader) (string, error) {
	size, err := readUint32(r)
	if err != nil {
		return "", err
	}
	if uint64(size) > uint64(r.Len()) {
		return "", io.ErrUnexpectedEOF
	}
	field := make([]byte, int(size))
	if _, err := io.ReadFull(r, field); err != nil {
		return "", err
	}
	return string(field), nil
}

func writeUint32(w io.Writer, value uint32) error {
	var buf [4]byte
	binary.BigEndian.PutUint32(buf[:], value)
	return writeAll(w, buf[:])
}

func readUint32(r io.Reader) (uint32, error) {
	var buf [4]byte
	if _, err := io.ReadFull(r, buf[:]); err != nil {
		return 0, err
	}
	return binary.BigEndian.Uint32(buf[:]), nil
}

func writeAll(w io.Writer, data []byte) error {
	for len(data) > 0 {
		n, err := w.Write(data)
		if n > 0 {
			data = data[n:]
		}
		if err != nil {
			return err
		}
		if n == 0 {
			return io.ErrShortWrite
		}
	}
	return nil
}
