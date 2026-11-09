# Asterisk migration: test checklist

`baseline_calls.py` covers the items marked **auto** on a throwaway install:
it runs the same scenarios on 1.8 and on the target version, and writes the phones' SIP
behaviour plus the CDR and `queue_log` rows to a report, so both can be diffed.
Items marked **real** need real equipment.

| Area | Item | How |
|---|---|---|
| Registration | Phones register with their SIP password | auto, real |
| Calls | Internal call, audio both ways, hangup | auto, real |
| Calls | Incoming and outgoing call over a trunk, correct caller ID | real |
| Calls | Callee busy (486), DND detection | auto |
| Call features | Forward always, busy, no answer (internal and external source) | auto (always, no answer) |
| Call features | Call waiting off: second caller is rejected | auto |
| Call features | Group and single-user pickup | auto |
| Call features | Blind and attended transfer | auto (blind), real (attended) |
| Call features | Parking | real |
| Call features | Call completion (`gs-cc-worker`) | real |
| Phone display | BLF lamps follow call state | auto, real |
| Phone display | MWI lamp follows voicemail state | auto, real |
| Phone display | Distinctive ringing (`Alert-Info`), autoanswer/intercom | real |
| Voicemail | Message recorded, stored in `vm_msgs`, mailbox access | auto (recording) |
| Queues | Call distributed to member, `queue_log`, login and logout, status pages | auto (call) |
| Conferences | First caller sets a PIN, others must enter it, wrong PIN rejected | auto |
| Conferences | Room cleared when the last caller leaves | auto |
| Fax | Send and receive via `iaxmodem`, PDF download | VM script, real line |
| Provisioning | Snom settings download, multicast auto-provisioning | real |
| Provisioning | Phone resync and reboot NOTIFY from the GUI | auto (NOTIFY) |
| GUI | Gateway status, queue monitor, extension state, statistics | real or browser |
| Statistics | Same calls give the same figures on 1.8 and the target version | compare reports |
| System | All services active after reboot, no errors in the logs | VM |
