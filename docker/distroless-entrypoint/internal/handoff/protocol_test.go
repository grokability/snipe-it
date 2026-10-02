package handoff

import (
	"bytes"
	"encoding/binary"
	"reflect"
	"strings"
	"testing"
)

func TestResultRoundTripsOpaqueEnvironmentBytes(t *testing.T) {
	want := Result{
		Disposition: StartServices,
		Environment: []EnvironmentEntry{
			{Name: "DB_PASSWORD", Value: "spaces and \"quotes\" and 'single quotes'; $(literal)\nsecond line=value"},
			{Name: "OPAQUE", Value: string([]byte{0xff, 0xfe, '\n', 'x'})},
		},
	}
	var wire bytes.Buffer
	if err := WriteResult(&wire, want); err != nil {
		t.Fatalf("WriteResult: %v", err)
	}
	got, err := ReadResult(bytes.NewReader(wire.Bytes()))
	if err != nil {
		t.Fatalf("ReadResult: %v", err)
	}
	if !reflect.DeepEqual(got, want) {
		t.Fatalf("round trip changed opaque environment bytes: got %#v, want %#v", got, want)
	}
}

func TestWriteResultRejectsInvalidEnvironment(t *testing.T) {
	cases := []struct {
		name  string
		entry EnvironmentEntry
	}{
		{"empty name", EnvironmentEntry{Name: "", Value: "x"}},
		{"equals in name", EnvironmentEntry{Name: "BAD=NAME", Value: "x"}},
		{"nul in name", EnvironmentEntry{Name: "BAD\x00NAME", Value: "x"}},
		{"nul in value", EnvironmentEntry{Name: "VALUE", Value: "bad\x00value"}},
	}
	for _, tc := range cases {
		t.Run(tc.name, func(t *testing.T) {
			var wire bytes.Buffer
			err := WriteResult(&wire, Result{Disposition: StartServices, Environment: []EnvironmentEntry{tc.entry}})
			if err == nil {
				t.Fatal("WriteResult accepted an invalid environment entry")
			}
		})
	}
}

func TestExitSuccessDoesNotCarryEnvironment(t *testing.T) {
	var wire bytes.Buffer
	err := WriteResult(&wire, Result{Disposition: ExitSuccess, Environment: []EnvironmentEntry{{Name: "APP_KEY", Value: "secret"}}})
	if err == nil {
		t.Fatal("ExitSuccess accepted child environment")
	}
}

func TestReadResultRequiresOneCompleteFrameAndEOF(t *testing.T) {
	encoded := rawFrame(StartServices, nil)

	t.Run("truncated", func(t *testing.T) {
		if _, err := ReadResult(bytes.NewReader(encoded[:len(encoded)-1])); err == nil {
			t.Fatal("ReadResult accepted a truncated frame")
		}
	})
	t.Run("trailing bytes", func(t *testing.T) {
		input := append(append([]byte(nil), encoded...), 0x7f)
		if _, err := ReadResult(bytes.NewReader(input)); err == nil {
			t.Fatal("ReadResult accepted trailing bytes")
		}
	})
	t.Run("missing frame", func(t *testing.T) {
		if _, err := ReadResult(bytes.NewReader(nil)); err == nil {
			t.Fatal("ReadResult accepted EOF before a frame")
		}
	})
}

func TestReadResultRejectsMalformedHeaderAndEnvironment(t *testing.T) {
	t.Run("unsupported version", func(t *testing.T) {
		frame := rawFrame(StartServices, nil)
		frame[4]++
		if _, err := ReadResult(bytes.NewReader(frame)); err == nil {
			t.Fatal("ReadResult accepted an unsupported version")
		}
	})
	t.Run("oversized header", func(t *testing.T) {
		var header [headerSize]byte
		copy(header[:4], frameMagic[:])
		header[4] = protocolVersion
		binary.BigEndian.PutUint32(header[5:], MaxPayloadBytes+1)
		if _, err := ReadResult(bytes.NewReader(header[:])); err == nil {
			t.Fatal("ReadResult accepted an oversized frame")
		}
	})
	t.Run("equals in name", func(t *testing.T) {
		if _, err := ReadResult(bytes.NewReader(rawFrame(StartServices, []EnvironmentEntry{{Name: "BAD=NAME", Value: "x"}}))); err == nil {
			t.Fatal("ReadResult accepted equals in environment name")
		}
	})
	t.Run("nul in name", func(t *testing.T) {
		if _, err := ReadResult(bytes.NewReader(rawFrame(StartServices, []EnvironmentEntry{{Name: "BAD\x00NAME", Value: "x"}}))); err == nil {
			t.Fatal("ReadResult accepted NUL in environment name")
		}
	})
	t.Run("nul in value", func(t *testing.T) {
		if _, err := ReadResult(bytes.NewReader(rawFrame(StartServices, []EnvironmentEntry{{Name: "VALUE", Value: "x\x00y"}}))); err == nil {
			t.Fatal("ReadResult accepted NUL in environment value")
		}
	})
}

func TestWriteResultBoundsPayload(t *testing.T) {
	var wire bytes.Buffer
	result := Result{Disposition: StartServices, Environment: []EnvironmentEntry{{Name: "LARGE", Value: strings.Repeat("x", MaxPayloadBytes+1)}}}
	if err := WriteResult(&wire, result); err == nil {
		t.Fatal("WriteResult accepted a payload larger than MaxPayloadBytes")
	}
}

func rawFrame(disposition Disposition, environment []EnvironmentEntry) []byte {
	var body bytes.Buffer
	body.WriteByte(byte(disposition))
	_ = writeUint32(&body, uint32(len(environment)))
	for _, entry := range environment {
		_ = writeField(&body, entry.Name)
		_ = writeField(&body, entry.Value)
	}
	var frame bytes.Buffer
	frame.Write(frameMagic[:])
	frame.WriteByte(protocolVersion)
	_ = writeUint32(&frame, uint32(body.Len()))
	frame.Write(body.Bytes())
	return frame.Bytes()
}

func TestInvalidDispositionIsRejected(t *testing.T) {
	var wire bytes.Buffer
	if err := WriteResult(&wire, Result{Disposition: 99}); err == nil {
		t.Fatal("WriteResult accepted an invalid disposition")
	}
	frame := rawFrame(StartServices, nil)
	frame[headerSize] = 99
	if _, err := ReadResult(bytes.NewReader(frame)); err == nil {
		t.Fatal("ReadResult accepted an invalid disposition")
	}
}
