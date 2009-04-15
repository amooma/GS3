# (c) Philipp Kempgen <philipp.kempgen@amooma.de>

SOX = sox

FILES_WAV  = $(shell find *.wav */*.wav -type f | grep -v ' ')
FILES_ALAW = $(FILES_WAV:%.wav=%.alaw)
FILES_ULAW = $(FILES_WAV:%.wav=%.ulaw)
FILES_GSM  = $(FILES_WAV:%.wav=%.gsm)


help:
	@echo "Targets:"
	@echo "    make alaw :  Convert to A-law"
	@echo "    make ulaw :  Convert to U-law"
	@echo "    make gsm  :  Convert to GSM"
	@echo ""

echo-wavs:
	@echo $(FILES_WAV)
echo-alaws:
	@echo $(FILES_ALAW)
echo-ulaws:
	@echo $(FILES_ULAW)
echo-gsms:
	@echo $(FILES_GSM)

%.alaw: %.wav
	$(SOX) -t wav $< -r 8000 -c 1 -w -A -t al $@

%.ulaw: %.wav
	$(SOX) -t wav $< -r 8000 -c 1 -w -U -t ul $@

%.gsm: %.wav
	$(SOX) -t wav $< -r 8000 -c 1 -w -g -t gsm $@ resample

wav:
alaw: $(FILES_ALAW)
ulaw: $(FILES_ULAW)
gsm:  $(FILES_GSM)

clean:
	rm $(FILES_ALAW) 2>>/dev/null || true
	rm $(FILES_ULAW) 2>>/dev/null || true
	rm $(FILES_GSM)  2>>/dev/null || true


.PHONY: help clean echo-wavs echo-alaws echo-ulaws echo-gsms wav alaw ulaw gsm

