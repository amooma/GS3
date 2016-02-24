Sample DHCP Server Config (edit IPs to fit your needs):



subnet 192.168.x.0 netmask 255.255.255.0 {
  range 192.168.x.100 192.168.x.150;

  # Snom-Phones, MAC starts with 00:04:13
  if binary-to-ascii(16, 32, "", substring(hardware, 0, 4)) = "1000413" {
    log(info, "request from snom phone, setting proper options.");
    # GS3
    option tftp-server-name "http://192.168.x.250";
    option bootfile-name "/gemeinschaft/prov/snom/settings.php?mac={mac}";
  }

  # Yealink-Phones, MAC starts with 00:15:65
  if binary-to-ascii(16, 32, "", substring(hardware, 0, 4)) = "1001565" {
    log(info, "request from yealink phone, setting proper options.");
    # GS3
    option tftp-server-name "http://192.168.x.250/gemeinschaft/prov/yealink/$MAC.cfg";
  }

}

