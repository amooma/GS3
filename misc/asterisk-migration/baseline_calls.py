#!/usr/bin/env python3
"""Repeatable call scenarios for comparing Gemeinschaft on Asterisk 1.8 and newer versions.

Run on the server itself:  baseline_calls.py <server-ip> <output-dir>
It creates test users 9997, a queue and a pickup group, so use a throwaway
system. For each scenario it records the SIP behaviour seen by the phones and
the resulting CDR and queue_log rows in <output-dir>.
"""
import hashlib, os, queue, random, re, socket, subprocess, sys, threading, time

SRV = sys.argv[1] if len(sys.argv) > 1 else '10.0.2.15'
OUT = sys.argv[2] if len(sys.argv) > 2 else '/root/baseline'
PORT = 5060

def md5(s): return hashlib.md5(s.encode()).hexdigest()
def sh(c): return subprocess.run(c, shell=True, capture_output=True, text=True).stdout
def ast(c): return sh("asterisk -rx '%s'" % c)
def secret(u): return sh("mysql -N asterisk -e \"SELECT secret FROM ast_sipfriends WHERE name='%s'\"" % u).strip()
results = []
def check(name, ok, info=''):
    results.append(ok); print('%-44s %-5s %s' % (name, 'ok' if ok else 'FAIL', info), flush=True)

class Phone:
    def __init__(self, user, autoanswer=True):
        self.user, self.pw, self.auto = user, secret(user), autoanswer
        self.sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM); self.sock.bind((SRV, 0)); self.port = self.sock.getsockname()[1]
        self.rtp = socket.socket(socket.AF_INET, socket.SOCK_DGRAM); self.rtp.bind((SRV, 0)); self.rtp_port = self.rtp.getsockname()[1]; self.rtp.settimeout(0.2)
        self.q = queue.Queue(); self.cseq = random.randint(100, 900); self.run = True; self.rx = 0; self.remote = None; self.media = False
        self.dialog = None; self.incoming = 0; self.got_bye = False; self.reject = None; self.invites = []; self.notifies = []
        threading.Thread(target=self.reader, daemon=True).start(); threading.Thread(target=self.rtp_rx, daemon=True).start(); threading.Thread(target=self.rtp_tx, daemon=True).start()
    def tag(self): return '%x' % random.getrandbits(40)
    def branch(self): return 'z9hG4bK%x' % random.getrandbits(48)
    def sdp(self):
        return ('v=0\r\no=- 1 1 IN IP4 %s\r\ns=t\r\nc=IN IP4 %s\r\nt=0 0\r\nm=audio %d RTP/AVP 8\r\na=rtpmap:8 PCMA/8000\r\na=sendrecv\r\n' % (SRV, SRV, self.rtp_port))
    def send(self, t): self.sock.sendto(t.encode(), (SRV, PORT))
    def rtp_rx(self):
        while self.run:
            try:
                d, a = self.rtp.recvfrom(2048)
                if len(d) > 12: self.rx += 1
            except socket.timeout: pass
            except OSError: return
    def rtp_tx(self):
        seq, ts, ssrc = random.randint(0, 999), 0, random.getrandbits(31)
        while self.run:
            if self.media and self.remote:
                h = bytes([0x80, 8]) + seq.to_bytes(2, 'big') + ts.to_bytes(4, 'big') + ssrc.to_bytes(4, 'big')
                try: self.rtp.sendto(h + (b'\x2a\xaa' * 80), self.remote)
                except OSError: pass
                seq = (seq + 1) & 0xffff; ts = (ts + 160) & 0xffffffff
            time.sleep(0.02)
    ALIAS = {'via': 'v', 'from': 'f', 'to': 't', 'call-id': 'i', 'contact': 'm', 'content-type': 'c', 'content-length': 'l', 'event': 'o'}
    @staticmethod
    def hdrs(m, n):
        head = m.split('\r\n\r\n', 1)[0]; a = Phone.ALIAS.get(n.lower()); names = n + ('|' + a if a else '')
        return re.findall(r'^(?:%s):[ \t]*(.*?)\r?$' % names, head, re.M | re.I)
    @staticmethod
    def hdr(m, n):
        r = Phone.hdrs(m, n); return r[0] if r else ''
    def set_remote(self, m):
        c = re.search(r'c=IN IP4 ([\d.]+)', m); p = re.search(r'm=audio (\d+)', m)
        if c and p: self.remote = (c.group(1), int(p.group(1)))
    def reply(self, m, code, reason, totag=None, body='', contact=True):
        to = self.hdr(m, 'To')
        if totag and 'tag=' not in to: to += ';tag=' + totag
        v = ''.join('Via: %s\r\n' % x for x in self.hdrs(m, 'Via'))
        rr = ''.join('Record-Route: %s\r\n' % x for x in self.hdrs(m, 'Record-Route'))
        ct = ('Contact: <sip:%s@%s:%d>\r\n' % (self.user, SRV, self.port)) if contact else ''
        ty = 'Content-Type: application/sdp\r\n' if body else ''
        self.send('SIP/2.0 %d %s\r\n%s%sFrom: %s\r\nTo: %s\r\nCall-ID: %s\r\nCSeq: %s\r\n%s%sContent-Length: %d\r\n\r\n%s' % (code, reason, v, rr, self.hdr(m, 'From'), to, self.hdr(m, 'Call-ID'), self.hdr(m, 'CSeq'), ct, ty, len(body), body))
    def reader(self):
        while self.run:
            try: d, _ = self.sock.recvfrom(65535)
            except OSError: return
            m = d.decode('utf-8', 'replace')
            if m.startswith('SIP/2.0'): self.q.put(m); continue
            meth = m.split(' ', 1)[0]
            if meth == 'INVITE':
                if self.hdr(m, 'To').find('tag=') >= 0:  # re-INVITE in dialog
                    self.set_remote(m); self.reply(m, 200, 'OK', body=self.sdp()); continue
                self.incoming += 1; self.invites.append(m)
                if self.reject:
                    tag = self.tag(); self.reply(m, 100, 'Trying', contact=False); self.reply(m, self.reject, 'Busy Here', totag=tag, contact=False); continue
                self.reply(m, 100, 'Trying', contact=False)
                tag = self.tag(); self.reply(m, 180, 'Ringing', totag=tag)
                if self.auto:
                    self.set_remote(m); self.media = True
                    self.reply(m, 200, 'OK', totag=tag, body=self.sdp())
                    self.dialog = dict(callid=self.hdr(m, 'Call-ID'), local=self.hdr(m, 'To') + ';tag=' + tag, remote=self.hdr(m, 'From'),
                                       target=re.search(r'<?(sip:[^>;\s]+)', self.hdr(m, 'Contact')).group(1))
            elif meth == 'BYE':
                self.reply(m, 200, 'OK', contact=False)
                if not self.dialog or self.hdr(m, 'Call-ID') == self.dialog['callid']:
                    self.media = False; self.got_bye = True; self.dialog = None
            elif meth == 'ACK': pass
            elif meth == 'NOTIFY':
                self.notifies.append(m); self.reply(m, 200, 'OK', contact=False)
            elif meth == 'CANCEL': self.reply(m, 200, 'OK', contact=False)
            else: self.reply(m, 200, 'OK')
    def wait(self, t=8):
        try: return self.q.get(timeout=t)
        except queue.Empty: return None
    def auth(self, chal, meth, uri):
        realm = re.search(r'realm="([^"]+)"', chal).group(1); nonce = re.search(r'nonce="([^"]+)"', chal).group(1)
        r = md5('%s:%s:%s' % (md5('%s:%s:%s' % (self.user, realm, self.pw)), nonce, md5('%s:%s' % (meth, uri))))
        return 'Digest username="%s", realm="%s", nonce="%s", uri="%s", response="%s", algorithm=MD5' % (self.user, realm, nonce, uri, r)
    def register(self):
        cid, ft, uri = 'r%x@t' % random.getrandbits(40), self.tag(), 'sip:%s' % SRV; a = ''
        for _ in range(3):
            self.cseq += 1
            self.send('REGISTER %s SIP/2.0\r\nVia: SIP/2.0/UDP %s:%d;branch=%s;rport\r\nMax-Forwards: 70\r\nFrom: <sip:%s@%s>;tag=%s\r\nTo: <sip:%s@%s>\r\nCall-ID: %s\r\nCSeq: %d REGISTER\r\nContact: <sip:%s@%s:%d>\r\nExpires: 300\r\n%sContent-Length: 0\r\n\r\n' % (uri, SRV, self.port, self.branch(), self.user, SRV, ft, self.user, SRV, cid, self.cseq, self.user, SRV, self.port, a))
            r = self.wait()
            if r is None: return 'timeout'
            code = int(r.split(' ', 2)[1])
            if code == 200: return 200
            if code in (401, 407):
                n = 'Authorization' if code == 401 else 'Proxy-Authorization'
                a = '%s: %s\r\n' % (n, self.auth(self.hdr(r, 'WWW-Authenticate') or self.hdr(r, 'Proxy-Authenticate'), 'REGISTER', uri)); continue
            return code
        return 'authfail'
    def invite_msg(self, num, cid, ft, br, cseq, a=''):
        uri = 'sip:%s@%s' % (num, SRV); b = self.sdp()
        return uri, 'INVITE %s SIP/2.0\r\nVia: SIP/2.0/UDP %s:%d;branch=%s;rport\r\nMax-Forwards: 70\r\nFrom: "%s" <sip:%s@%s>;tag=%s\r\nTo: <%s>\r\nCall-ID: %s\r\nCSeq: %d INVITE\r\nContact: <sip:%s@%s:%d>\r\n%sContent-Type: application/sdp\r\nContent-Length: %d\r\n\r\n%s' % (uri, SRV, self.port, br, self.user, self.user, SRV, ft, uri, cid, cseq, self.user, SRV, self.port, a, len(b), b)
    def call(self, num, timeout=25):
        """returns final response code; sets up self.dialog on 200"""
        cid, ft, br = 'c%x@t' % random.getrandbits(40), self.tag(), self.branch(); self.cseq += 1; a = ''
        uri, msg = self.invite_msg(num, cid, ft, br, self.cseq); self.send(msg); end = time.time() + timeout
        self.last = []
        while time.time() < end:
            r = self.wait(end - time.time())
            if r is None: break
            if 'INVITE' not in self.hdr(r, 'CSeq') or self.hdr(r, 'Call-ID') != cid: continue
            code = int(r.split(' ', 2)[1]); self.last.append(code)
            if code < 200:
                if code == 183: self.set_remote(r); self.media = bool(self.remote)
                continue
            ack = 'ACK %s SIP/2.0\r\nVia: SIP/2.0/UDP %s:%d;branch=%s;rport\r\nMax-Forwards: 70\r\nFrom: %s\r\nTo: %s\r\nCall-ID: %s\r\nCSeq: %s ACK\r\n%sContent-Length: 0\r\n\r\n'
            cs = self.hdr(r, 'CSeq').split()[0]
            if code in (401, 407):
                self.send(ack % (uri, SRV, self.port, br, self.hdr(r, 'From'), self.hdr(r, 'To'), cid, cs, ''))
                n = 'Authorization' if code == 401 else 'Proxy-Authorization'
                a = '%s: %s\r\n' % (n, self.auth(self.hdr(r, 'WWW-Authenticate') or self.hdr(r, 'Proxy-Authenticate'), 'INVITE', uri))
                self.cseq += 1; br = self.branch(); _, msg = self.invite_msg(num, cid, ft, br, self.cseq, a); self.send(msg); continue
            if code == 200:
                target = re.search(r'<?(sip:[^>;\s]+)', self.hdr(r, 'Contact')).group(1)
                self.set_remote(r); self.media = True
                self.send(ack % (target, SRV, self.port, self.branch(), self.hdr(r, 'From'), self.hdr(r, 'To'), cid, cs, ''))
                self.dialog = dict(callid=cid, local=self.hdr(r, 'From'), remote=self.hdr(r, 'To'), target=target); return 200
            self.send(ack % (uri, SRV, self.port, br, self.hdr(r, 'From'), self.hdr(r, 'To'), cid, cs, '')); return code
        return 'timeout'
    def bye(self):
        d = self.dialog
        if not d: return None
        self.cseq += 1; self.media = False
        self.send('BYE %s SIP/2.0\r\nVia: SIP/2.0/UDP %s:%d;branch=%s;rport\r\nMax-Forwards: 70\r\nFrom: %s\r\nTo: %s\r\nCall-ID: %s\r\nCSeq: %d BYE\r\nContent-Length: 0\r\n\r\n' % (d['target'], SRV, self.port, self.branch(), d['local'], d['remote'], d['callid'], self.cseq))
        self.dialog = None; r = self.wait(); return int(r.split(' ', 2)[1]) if r else 'timeout'
    def dtmf(self, digit):
        d = self.dialog
        if not d: return None
        self.cseq += 1; b = 'Signal=%s\r\nDuration=160\r\n' % digit
        self.send('INFO %s SIP/2.0\r\nVia: SIP/2.0/UDP %s:%d;branch=%s;rport\r\nMax-Forwards: 70\r\nFrom: %s\r\nTo: %s\r\nCall-ID: %s\r\nCSeq: %d INFO\r\nContent-Type: application/dtmf-relay\r\nContent-Length: %d\r\n\r\n%s' % (d['target'], SRV, self.port, self.branch(), d['local'], d['remote'], d['callid'], self.cseq, len(b), b))
        return self.wait(3)
    def subscribe(self, target, event, accept):
        cid, ft, uri = 's%x@t' % random.getrandbits(40), self.tag(), 'sip:%s@%s' % (target, SRV); a = ''
        for _ in range(3):
            self.cseq += 1
            self.send('SUBSCRIBE %s SIP/2.0\r\nVia: SIP/2.0/UDP %s:%d;branch=%s;rport\r\nMax-Forwards: 70\r\nFrom: <sip:%s@%s>;tag=%s\r\nTo: <%s>\r\nCall-ID: %s\r\nCSeq: %d SUBSCRIBE\r\nContact: <sip:%s@%s:%d>\r\nEvent: %s\r\nAccept: %s\r\nExpires: 600\r\n%sContent-Length: 0\r\n\r\n' % (uri, SRV, self.port, self.branch(), self.user, SRV, ft, uri, cid, self.cseq, self.user, SRV, self.port, event, accept, a))
            r = self.wait()
            if r is None: return 'timeout'
            code = int(r.split(' ', 2)[1])
            if code in (401, 407):
                n = 'Authorization' if code == 401 else 'Proxy-Authorization'
                a = '%s: %s\r\n' % (n, self.auth(self.hdr(r, 'WWW-Authenticate') or self.hdr(r, 'Proxy-Authenticate'), 'SUBSCRIBE', uri)); continue
            return code
        return 'authfail'
    def refer(self, target):
        d = self.dialog; self.cseq += 1
        self.send('REFER %s SIP/2.0\r\nVia: SIP/2.0/UDP %s:%d;branch=%s;rport\r\nMax-Forwards: 70\r\nFrom: %s\r\nTo: %s\r\nCall-ID: %s\r\nCSeq: %d REFER\r\nContact: <sip:%s@%s:%d>\r\nRefer-To: <sip:%s@%s>\r\nReferred-By: <sip:%s@%s>\r\nContent-Length: 0\r\n\r\n' % (d['target'], SRV, self.port, self.branch(), d['local'], d['remote'], d['callid'], self.cseq, self.user, SRV, self.port, target, SRV, self.user, SRV))
        r = self.wait(); return int(r.split(' ', 2)[1]) if r else 'timeout'
    def stop(self):
        self.run = False; self.sock.close(); self.rtp.close()


def mysql(q, db='asterisk'): return sh('mysql -N %s -e "%s"' % (db, q))
ver = ast('core show version')
TECH = 'SIP' if 'Asterisk 1.' in ver else 'PJSIP'
NOTIFY_CMD = 'sip notify snom-check-cfg %s' if TECH == 'SIP' else 'pjsip send notify snom-check-cfg endpoint %s'
os.makedirs(OUT, exist_ok=True)
report = open(os.path.join(OUT, 'report.txt'), 'w')
def log(t):
    print(t, flush=True); report.write(t + '\n'); report.flush()

CDR_COLS = 'src,dst,dcontext,channel,dstchannel,lastapp,lastdata,disposition,billsec,amaflags,accountcode,userfield'
def cdr_top(): return int(mysql('SELECT COALESCE(MAX(uniqueid+0),0) FROM ast_cdr').strip() or 0)
def qlog_len():
    try: return sum(1 for _ in open('/var/log/asterisk/queue_log', errors='replace'))
    except OSError: return 0
marks = {}
PHONES = []
def begin(name):
    for p in PHONES: p.register()   # real phones re-register too
    time.sleep(1 if PHONES else 0)
    log('\n===== ' + name); marks['cdr'] = mysql('SELECT COUNT(*) FROM ast_cdr').strip(); marks['q'] = qlog_len()
def end():
    time.sleep(6)
    rows = mysql('SELECT %s FROM ast_cdr ORDER BY calldate, uniqueid LIMIT 1000 OFFSET %s' % (CDR_COLS, marks['cdr'])).strip()
    log('CDR:'); [log('  ' + r) for r in rows.splitlines()]
    try: q = open('/var/log/asterisk/queue_log', errors='replace').read().splitlines()[marks['q']:]
    except OSError: q = []
    log('QUEUE_LOG:'); [log('  ' + re.sub(r'^\d+\|[\d.]+\|', '', x)) for x in q]
def rec(name, ok, info=''):
    check(name, ok, info); report.write('%-44s %-5s %s\n' % (name, 'ok' if ok else 'FAIL', info)); report.flush()
INTERESTING = ('Alert-Info', 'Call-Info', 'P-Asserted-Identity', 'Privacy', 'From', 'X-GS-user_id')
def show_invite(p):
    if p.invites:
        for h in INTERESTING:
            v = Phone.hdr(p.invites[-1], h)
            if v: log('  %s INVITE %s: %s' % (p.user, h, re.sub(r'tag=\w+', 'tag=*', v)))
def reset(*ps):
    for p in ps: p.invites, p.notifies, p.got_bye, p.reject, p.auto, p.incoming = [], [], False, None, True, 0
def scripts(c): return sh('cd /opt/gemeinschaft/scripts; %s 2>&1' % c).strip()

host = mysql('SELECT id FROM hosts ORDER BY id LIMIT 1').strip()
if not mysql("SELECT name FROM ast_sipfriends WHERE name='9997'").strip():
    log(scripts('./gs-user-add --user=third --ext=9997 --pin=123456 --firstname=Third --lastname=Tester --language=de --email=t@example.org --host=%s' % host)[-100:])
log(scripts('./gs-queue-add --queue=5000 --title=baseline --maxlen=5 --host=%s' % host)[-100:])
mysql("INSERT IGNORE INTO ast_queue_members (queue_name,_queue_id,interface,_user_id) SELECT '5000',q._id,'%s/9998',s._user_id FROM ast_queues q, ast_sipfriends s WHERE q.name='5000' AND s.name='9998'" % TECH)
scripts('./gs-pickupgroup-add --title=baseline')
pg = mysql("SELECT id FROM pickupgroups WHERE title='baseline' ORDER BY id DESC LIMIT 1").strip()
for u in ('admin', 'user', 'third'): scripts('./gs-pickupgroup-user-add --pgroup=%s --user=%s' % (pg, u))
sh('/opt/gemeinschaft/sbin/gs-ast-reload 2>&1'); time.sleep(8)

A, B, C = Phone('9999'), Phone('9998'), Phone('9997')
PHONES.extend((A, B, C))
begin('registration and BLF subscription')
for p in (A, B, C): rec('REGISTER ' + p.user, p.register() == 200)
time.sleep(3)
rec('SUBSCRIBE dialog for 9998', A.subscribe('9998', 'dialog', 'application/dialog-info+xml') == 200)
rec('SUBSCRIBE MWI for 9998', B.subscribe('9998', 'message-summary', 'application/simple-message-summary') in (200, 202), '')
time.sleep(3); end()

def simple_call(num, caller=A, hold=3):
    c = caller.call(num); info = 'responses %s final %s' % (caller.last, c)
    if c == 200: time.sleep(hold); caller.bye()
    return c, info

begin('internal call answered'); reset(A, B, C); A.notifies = []
c, i = simple_call('9998'); rec('answered', c == 200, i); time.sleep(4); show_invite(B); rec('callee got BYE', B.got_bye)
states = re.findall(r'<state[^>]*>(\w+)</state>', ' '.join(A.notifies)); log('  BLF states seen by 9999: %s' % states); end()

begin('callee busy (486 with no other call)'); reset(A, B, C); B.reject = 486
c, i = simple_call('9998'); log('  caller result: ' + i); end()

begin('forward always to 9997'); reset(A, B, C)
scripts('./gs-callforward-set --user=user --source=internal --case=always --type=std --number=9997'); scripts('./gs-callforward-activate --user=user --source=internal --case=always --active=std')
c, i = simple_call('9998'); rec('answered', c == 200, i); rec('forward target rang', C.incoming > 0); show_invite(C)
scripts('./gs-callforward-activate --user=user --source=internal --case=always --active=no'); end()

begin('call waiting off: second call to busy user'); reset(A, B, C); scripts('./gs-callwaiting-activate --user=user --active=no')
c = A.call('9998'); rec('first call answered', c == 200); time.sleep(2)
c2, i2 = simple_call('9998', caller=C, hold=1); log('  second caller result: ' + i2); A.bye(); end()

begin('queue call'); reset(A, B, C); before = B.incoming
c, i = simple_call('5000', hold=4); rec('queue answered', c == 200, i); rec('member rang', B.incoming > before); show_invite(B); end()

begin('pickup of ringing phone'); reset(A, B, C); B.auto = False
th = {}; t = threading.Thread(target=lambda: th.update(r=A.call('9998', timeout=20))); t.start(); time.sleep(3)
c, i = simple_call('*81*9998', caller=C, hold=2); log('  pickup result: ' + i); B.auto = True; t.join(timeout=25); log('  original caller result: %s' % th.get('r'))
if A.dialog: A.bye()
end()

begin('blind transfer'); reset(A, B, C)
c = A.call('9998'); rec('call answered', c == 200); time.sleep(2)
r = B.refer('9997'); log('  REFER response: %s' % r); time.sleep(5); rec('transfer target rang', C.incoming > 0); B.bye(); time.sleep(2)   # the transferring phone hangs up
for p in (A, C):
    if p.dialog: p.bye()
    time.sleep(0.5)
end()

def conf_count():
    t = ast('meetme list 123' if TECH == 'SIP' else 'confbridge list 123'); return len([l for l in t.splitlines() if re.match(r'^(User #:|\s*\d+|.*(PJSIP|SIP)/)', l)])
begin('conference with caller-set PIN'); reset(A, B, C)
c1 = A.call('88123'); rec('first caller connects', c1 == 200); time.sleep(4)
for d in '1234#': A.dtmf(d); time.sleep(0.4)
time.sleep(3)
c2 = B.call('88123'); rec('second caller connects', c2 == 200); time.sleep(4)
for d in '9999#': B.dtmf(d); time.sleep(0.4)
time.sleep(3); wrong = ast('meetme list 123' if TECH == 'SIP' else 'confbridge list 123'); log('  after wrong PIN: ' + ' | '.join(wrong.split('\n')[:4])[:220])
for d in '1234#': B.dtmf(d); time.sleep(0.4)
time.sleep(4); right = ast('meetme list 123' if TECH == 'SIP' else 'confbridge list 123'); log('  after right PIN: ' + ' | '.join(right.split('\n')[:4])[:220])
def users(t): return len(re.findall(r'(?:PJSIP|SIP)/99', t))
rec('wrong PIN keeps caller out', users(wrong) == 1, str(users(wrong)))
rec('right PIN lets caller in', users(right) == 2, str(users(right)))
reset(C); c3 = C.call('88123'); rec('third caller connects', c3 == 200); time.sleep(4)
for t in range(3):
    for d in '0000#': C.dtmf(d); time.sleep(0.4)
    time.sleep(4)
time.sleep(4); rec('three wrong PINs hang up', C.got_bye or C.dialog is None, ''); 
A.bye(); B.bye(); time.sleep(6)
gone = ast('meetme list 123' if TECH == 'SIP' else 'confbridge list 123'); rec('room closed', users(gone) == 0, gone.strip()[:60])
reset(A, B); c1 = A.call('88123'); time.sleep(4)
for d in '5555#': A.dtmf(d); time.sleep(0.4)
time.sleep(3); c2 = B.call('88123'); time.sleep(4)
for d in '5555#': B.dtmf(d); time.sleep(0.4)
time.sleep(4); again = ast('meetme list 123' if TECH == 'SIP' else 'confbridge list 123'); rec('new PIN works after room was freed', users(again) == 2, str(users(again)))
A.bye(); B.bye(); time.sleep(2); end()

begin('NOTIFY to phone (provisioning resync)'); reset(A, B, C); out = ast(NOTIFY_CMD % '9998'); time.sleep(2)
ev = [Phone.hdr(n, 'Event') for n in B.notifies]; rec('phone received NOTIFY', 'check-sync' in ' '.join(ev), str(ev)); end()

begin('no answer to voicemail, MWI'); reset(A, B, C); B.auto = False
scripts('./gs-callforward-set --user=user --source=internal --case=unavail --timeout=5 --type=vml --number=vm9998'); scripts('./gs-callforward-activate --user=user --source=internal --case=unavail --active=vml')
n0 = mysql('SELECT COUNT(*) FROM vm_msgs').strip(); c = A.call('9998', timeout=60); rec('diverted to voicemail', c == 200, 'phone rang %d time(s)' % B.incoming)
if c == 200: time.sleep(50); A.bye(); time.sleep(8)
n1 = mysql('SELECT COUNT(*) FROM vm_msgs').strip(); rec('message stored', n1 != n0, '%s -> %s' % (n0, n1))
log('  MWI: %s' % [re.search(r'Messages-Waiting: \w+', x).group(0) for x in B.notifies if 'Messages-Waiting' in x][-2:]); end()

for p in (A, B, C): p.stop()
log('\nRESULT: %d of %d passed' % (sum(results), len(results))); report.close()
