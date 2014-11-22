<?

#####################################################################
# /tmp/download.cfg
#####################################################################
/*if ( in_array($phone_model, array('ip280'), true) ) {
	psetting('ContactList|path', '/tmp/download.cfg');
	#to specify a XML phonebook for update
	#an example for a right server_address:http://192.168.0.132:9/file_provision/contactData1.xml
	psetting('ContactList|server_address', $prov_url_yealink.'contactData1.xml');
}*/


#####################################################################
# /yealink/config/Setting/autop.cfg
#####################################################################

# autop_mode
psetting('autop_mode|path', '/yealink/config/Setting/autop.cfg');
psetting('autop_mode|mode', '6');	# 0 = Disabled, 1 = Power On, 4 = Repeatedly, 5 = Weekly, 6 = Powen On + Repeatedly, 7 = Power On + Weekly
psetting('autop_mode|schedule_min', '120');	# if mode 4 or 6, by minutes (1 to 43200)
//psetting('autop_mode|schedule_dayofweek', '');	# if mode 5 or 7
//psetting('autop_mode|schedule_time', '');	# if mode 5 or 7
//psetting('autop_mode|schedule_time_end', '');	# if mode 5 or 7

# PNP
psetting('PNP|path', '/yealink/config/Setting/autop.cfg');
psetting('PNP|Pnp', '0');	# 0 = disabled, 1 = enabled , default 0

# custom_option
psetting('custom_option|path', '/yealink/config/Setting/autop.cfg');
psetting('custom_option|custom_option_code0', '');	# Integer from 129 to 254
psetting('custom_option|custom_option_type0', '1');	# 0 = IP Adress, 1 = String, default 0

# AES_KEY
psetting('AES_KEY|path', '/yealink/config/Setting/autop.cfg');
psetting('AES_KEY|aes_key_16', '');
psetting('AES_KEY|aes_key_16_mac', '');

# autoprovision
psetting('autoprovision|path', '/yealink/config/Setting/autop.cfg');
psetting('autoprovision|server_address', $prov_url_yealink);	# Autoprovision URL
psetting('autoprovision|user', '');				# HTTP User
psetting('autoprovision|password', '');				# HTPP Password

# AdminPassword
psetting('AdminPassword|path', '/yealink/config/Setting/autop.cfg');
psetting('AdminPassword|password', gs_get_conf('GS_TIPTEL_PROV_HTTP_PASS') );	# Admin Password

# UserPassword
psetting('UserPassword|path', '/yealink/config/Setting/autop.cfg');
psetting('UserPassword|password', 'user' );					# User Password


#####################################################################
# /yealink/config/Setting/Setting.cfg
#####################################################################

# Lang
psetting('Lang|path', '/yealink/config/Setting/Setting.cfg');
psetting('Lang|WebLanguage', 'English');	# web interface
psetting('Lang|ActiveWebLanguage', _yealink_astlang_to_yealinklang($user['language']) );	# lcd

# Time
psetting('Time|path', '/yealink/config/Setting/Setting.cfg');
psetting('Time|TimeZone', '+'.( ((int)date('Z')) / 3600) );	# -11 to +23
psetting('Time|TimeServer1', gs_get_conf('GS_TIPTEL_PROV_NTP') );	# NTP Server 1
//psetting('Time|TimeServer2', '');		# NTP Server 2
psetting('Time|Interval', '1000');		# in sec, default 1000
psetting('Time|SummerTime', '0');		# 0 = disable, 1 = enable, 2 = automatic
//psetting('Time|DSTTimeType', '0');		# 0 = by date, 1 = by week
//psetting('Time|StartTime', '');		# MM/DD/HH, default 1/1/0
//psetting('Time|EndTime', '');			# MM/DD/HH, default 12/31/23
//psetting('Time|OffSetTime', '');		# -300 to 300
psetting('Time|TimeFormat', '1');		# 0 = 12h, 1 = 24h
psetting('Time|DateFormat', '6');		# 0 = WWW MMM DD, 1 = DD-MMM-YY, 2 = YYYY-MM-DD, 3 = DD/MM/YYYY, 4 = MM/DD/YY, 5 = DD MMM YYYY, 6 = WWW DD MMM

# PhoneSettings
psetting('PhoneSetting|path', '/yealink/config/Setting/Setting.cfg');
psetting('PhoneSetting|InterDigitTime', '4');	# default 4
psetting('PhoneSetting|FlashHookTimer', '1');	# 0 to 800ms, default 1
psetting('PhoneSetting|Lock', '0');		# 0 = disable, 1 = Menu Key, 2 = Function Key, 3 = Talk call only
psetting('PhoneSetting|Ringtype', 'Ring1.wav');	# default ring
psetting('PhoneSetting|Contrast', '6');		# 1 to 10, default 6
psetting('PhoneSetting|Backlight', '2');	# 1,2 or 3, default 2
psetting('PhoneSetting|BacklightTime', '60');	# 15, 30, 60, 120 sec
//psetting('PhoneSetting|ProductName', '');	#
psetting('PhoneSetting|RingVol', '8');		# 0 to 15, default 8
psetting('PhoneSetting|HandFreeSpkVol', '8');	# 0 to 15, default 8
psetting('PhoneSetting|HandFreeMicVol', '8');	# 0 to 15, default 8
psetting('PhoneSetting|HandSetSpkVol', '8');	# 0 to 15, default 8
psetting('PhoneSetting|HandSetMicVol', '8');	# 0 to 15, default 8
psetting('PhoneSetting|HeadSetSpkVol', '8');	# 0 to 15, default 8
psetting('PhoneSetting|HeadSetMicVol', '8');	# 0 to 15, default 8

# SignalToneVol
psetting('SignalTonVol|path', '/yealink/config/Setting/setting.cfg');
psetting('SignalTonVol|Handset', '8');	# 0 to 15, default 8
psetting('SignalTonVol|Headset', '8');	# 0 to 15, default 8
psetting('SignalTonVol|Handfree', '8');	# 0 to 15, default 8

# RemotePhoneBook0
psetting('RemotePhoneBook0|path', '/yealink/config/Setting/Setting.cfg');
psetting('RemotePhoneBook0|URL', $prov_url_yealink.'pb_on_phone.php?m='.$mac.'&t=gs');
psetting('RemotePhoneBook0|Name', gs_get_conf('GS_PB_INTERNAL_TITLE', __("Intern")) );

# RemotePhoneBook1
psetting('RemotePhoneBook1|path', '/yealink/config/Setting/Setting.cfg');
psetting('RemotePhoneBook1|URL', $prov_url_yealink.'pb_on_phone.php?m='.$mac.'&t=prv');
psetting('RemotePhoneBook1|Name', gs_get_conf('GS_PB_PRIVATE_TITLE' , __("Pers\xC3\xB6nlich")) );

# RemotePhoneBook2
psetting('RemotePhoneBook2|path', '/yealink/config/Setting/Setting.cfg');
if ( gs_get_conf('GS_PB_IMPORTED_ENABLED') ) {
	psetting('RemotePhoneBook2|URL', $prov_url_yealink.'pb_on_phone.php?m='.$mac.'&t=imported');
	psetting('RemotePhoneBook2|Name', gs_get_conf('GS_PB_IMPORTED_TITLE', __("Extern")) );
} else {
	psetting('RemotePhoneBook2|URL', '');
	psetting('RemotePhoneBook2|Name', '');
}

# RemotePhoneBook3
psetting('RemotePhoneBook3|path', '/yealink/config/Setting/Setting.cfg');
psetting('RemotePhoneBook3|URL', '');
psetting('RemotePhoneBook3|Name', '');

# RemotePhoneBook4
psetting('RemotePhoneBook4|path', '/yealink/config/Setting/Setting.cfg');
psetting('RemotePhoneBook4|URL', '');
psetting('RemotePhoneBook4|Name', '');


#####################################################################
#  /yealink/config/Network/Network.cfg
#####################################################################

# WAN
psetting('WAN|path', '/yealink/config/Network/Network.cfg');
psetting('WAN|WANType', '0');	# 0 = DHCP, 1 = PPPoE, 2 = StaticIP
//psetting('WAN|WANStaticIP', '');
//psetting('WAN|WANSubnetMask', '');
//psetting('WAN|WANDefaultGateway', '');

# DNS
//psetting('DNS|path', '/yealink/config/Network/Network.cfg');
//psetting('DNS|PrimaryDNS', '');
//psetting('DNS|SecondaryDNS', '');

# PPPoE
//psetting('PPPoE|path', '/yealink/config/Network/Network.cfg');
//psetting('PPPoE|PPPoEUser', '');
//psetting('PPPoE|PPPoEPWD', '');

# LAN
psetting('LAN|path', '/yealink/config/Network/Network.cfg');
psetting('LAN|LANTYPE', '1');	# 0 = Router, 1 = Bridge
//psetting('LAN|RouterIP', '10.0.0.1');
//psetting('LAN|LANSubnetMask', '255.255.255.0');
//psetting('LAN|EnableDHCP', '1');
//psetting('LAN|DHCPStartIP', '10.0.0.10');
//psetting('LAN|DHCPEndIP', '10.0.0.100');

# VLAN
psetting('VLAN|path', '/yealink/config/Network/Network.cfg');
psetting('VLAN|ISVLAN', '0');			# 0 = disable, 1 = enable
psetting('VLAN|VID', '0');			# 0 to 4094
psetting('VLAN|USRPRIORITY', '0');		# 0 to 7
psetting('VLAN|PC_PORT_VLAN_ENABLE', '0');	# 0 = disable, 1 = enable
psetting('VLAN|PC_PORT_VID', '0');		# 0 to 4096
psetting('VLAN|PC_PORT_PRIORITY', '0');		# 0 to 7

# QOS
psetting('QOS|path', '/yealink/config/Network/Network.cfg');
psetting('QOS|RTPTOS', '40');		# 0 to 63, default 40
psetting('QOS|SIGNALTOS', '40');	# 0 to 63, default 40

# RTPPORT
psetting('RTPPORT|path', '/yealink/config/Network/Network.cfg');
psetting('RTPPORT|MaxRTPPort', '11800');	# 0 to 65535, default 11800
psetting('RTPPORT|MinRTPPort', '11780');	# 0 to 65535, default 11780

# SYSLOG
psetting('SYSLOG|path', '/yealink/config/Network/Network.cfg');
psetting('SYSLOG|SyslogdIP', '');	# IP Address

# telnet
psetting('telnet|path', '/yealink/config/Network/Network.cfg');
psetting('telnet|telnet_enable', '1');	# 0 = disable, 1 = enable


#####################################################################
#  /yealink/config/Features/Forward.cfg
#####################################################################

# AlwaysFWD
psetting('AlwaysFWD|path', '/yealink/config/Features/Forward.cfg');
psetting('AlwaysFWD|Enable', '0');	# 0 = disable, 1 = enable
psetting('AlwaysFWD|Target', '');
psetting('AlwaysFWD|On_Code', '');
psetting('AlwaysFWD|Off_Code', '');

# BusyFWD
psetting('BusyFWD|path', '/yealink/config/Features/Forward.cfg');
psetting('BusyFWD|Enable', '0');	# 0 = disable, 1 = enable
psetting('BusyFWD|Target', '');
psetting('BusyFWD|On_Code', '');
psetting('BusyFWD|Off_Code', '');

# TimeoutFWD
psetting('TimeoutFWD|path', '/yealink/config/Features/Forward.cfg');
psetting('TimeoutFWD|Enable', '0');	# 0 = disable, 1 = enable
psetting('TimeoutFWD|Target', '');
psetting('TimeoutFWD|On_Code', '');
psetting('TimeoutFWD|Off_Code', '');
psetting('TimeoutFWD|Timeout', '10');	# 5, 10 or 15 sec


#####################################################################
#  /yealink/config/Features/Phone.cfg
#####################################################################

# Features
psetting('Features|path', '/yealink/config/Features/Phone.cfg');
$dnd = '0';
/*$cf = gs_callforward_get( $user['user'] );
if (! isGsError($cf) && is_array($cf)) {
	if ( @$cf['internal']['always']['active'] != 'no'
	  || @$cf['external']['always']['active'] != 'no' )
	{
		$dnd = '1';  //FIXME - bad hack!
	}                                                  
}*/
psetting('Features|DND', $dnd);			# 0 = disable, 1 = enable  //don't function FIXME
# call waiting (Anklopfen) activate ? //FIXME
//$callwaiting = (int)$db->executeGetOne( 'SELECT `active` FROM `callwaiting` WHERE `user_id`='. $user_id );
//psetting('Features|Call_Waiting', ($callwaiting ? '1' : '0') );	# 0 = disable, 1 = enable
psetting('Features|Call_Waiting', '1');		# 0 = disable, 1 = enable
psetting('Features|EnableHotline', '0');	# 0 = disable, 1 = enable
psetting('Features|Hotlinenumber', '');		# Hotline Number ???
psetting('Features|BusyToneDelay', '0');	# 0, 3 or 5 sec
psetting('Features|Refuse_Code', '486');	# 404 = Not found, 480 = Temporarliy not available, 486 = Busy here
psetting('Features|DND_Code', '480');		# 404 = Not found, 480 = Temporarliy not available, 486 = Busy here
psetting('Features|DND_On_Code', 'dnd-on');	# SIP dial when press dnd-button
psetting('Features|DND_Off_Code', 'dnd-off');	# SIP dial when press dnd-button
psetting('Features|AllowIntercom', '1');	# 0 = disable, 1 = enable
psetting('Features|IntercomMute', '0');		# 0 = disable, 1 = enable
psetting('Features|IntercomTone', '1');		# 0 = disable, 1 = enable
psetting('Features|IntercomBarge', '1');	# 0 = disable, 1 = enable
psetting('Features|ButtonSoundOn', '1');	# 0 = disable, 1 = enable

# AutoRedial
psetting('AutoRedial|path', '/yealink/config/Features/Phone.cfg');
psetting('AutoRedial|EnableRedial', '0');	# 0 = disable, 1 = enable
psetting('AutoRedial|RedialInterval', '30');	# 1 to 300s, default 30
psetting('AutoRedial|RedialTimes', '10');	# 1 to 300, default 10

# AutoAnswer
psetting('AutoAnswer|path', '/yealink/config/Features/Phone.cfg');
psetting('AutoAnswer|Enable', '0');		# 0 = disable, 1 = enable

# PoundSend
psetting('PoundSend|path', '/yealink/config/Features/Phone.cfg');
psetting('PoundSend|Enable', '1');		# 0 = disable, 1 = #, 2 = *

# Emergency
psetting('Emergency|path', '/yealink/config/Features/Phone.cfg');
psetting('Emergency|Num', '');			# Emergency Number ???

# RingerDevice
psetting('RingerDevice|path', '/yealink/config/Features/Phone.cfg');
psetting('RingerDevice|IsUseHeadset', '0');	# 0 = use speaker, 1 = use headset


#####################################################################
#  /yealink/config/Features/Message.cfg
#####################################################################
psetting('Message|path', '/yealink/config/Features/Message.cfg');
psetting('Message|VoiceNumber0', 'voicemail');
psetting('Message|VoiceNumber1', '');
psetting('Message|VoiceNumber2', '');
psetting('Message|VoiceNumber3', '');
psetting('Message|VoiceNumber4', '');
psetting('Message|VoiceNumber5', '');


#####################################################################
#  /yealink/config/voip/tone.ini
#####################################################################
psetting('Country|path', '/yealink/config/voip/tone.ini');
psetting('Country|Country', 'Germany');


#####################################################################
#  /yealink/config/Advanced/Advanced.cfg
#####################################################################
psetting('Webserver Type|path', '/yealink/config/Advanced/Advanced.cfg');
psetting('Webserver Type|WebType', '1');	# 0 = disabled, 1 = HTTP&HTTPS, 2 = HTTP, 3 = HTTPS

psetting('Advanced|path', '/yealink/config/Advanced/Advanced.cfg');
psetting('Advanced|var_enabled', '1');


#####################################################################
#  /yealink/config/WebItemsLevel.cfg
#####################################################################
# 0 = item is visible in all access level (user, var, admin)
# 1 = item is visible in admin and var level
# 2 = item is only visible in admin level

psetting('Phone|path', '/yealink/config/WebItemsLevel.cfg');
psetting('Phone|features', '2');
psetting('Phone|SMS', '2');


#####################################################################
# /yealink/config/voip/sipAccount0.cfg
#####################################################################

# account
psetting('account|path', '/yealink/config/voip/sipAccount0.cfg');
psetting('account|Enable', '1');		# 0 = disable, 1 = enable
psetting('account|Label', $user_ext .' '. mb_subStr($user['firstname'],0,1) .'. '. $user['lastname']);
psetting('account|DisplayName', $user['callerid']);
psetting('account|UserName', $user_ext);
psetting('account|AuthName', $user_ext);
psetting('account|Password', $user['secret']);
psetting('account|SIPServerHost', $host);
psetting('account|SIPServerPort', '5060');
psetting('account|UseOutboundProxy', '0');	# 0 = disable, 1 = enable
psetting('account|OutboundHost', '');
psetting('account|OutboundPort', '5061');
psetting('account|Transport', '0');		# 0 = UDP, 1 = TCP, 2 = TLS
psetting('account|BakOutboundHost', '');
psetting('account|BakOutboundPort', '5062');
psetting('account|proxy-require', '');
psetting('account|AnonymousCall', '0');		# 0 = disable, 1 = enable
psetting('account|RejectAnonymousCall', '0');	# 0 = disable, 1 = enable
psetting('account|Expire', '3600');
psetting('account|SIPListenPort', '5060');
psetting('account|Enable 100Rel', '0');		# 0 = disable, 1 = enable
psetting('account|precondition', '0');		# 0 = disable, 1 = enable
psetting('account|SubsribeRegister', '1');	# 0 = disable, 1 = enable
psetting('account|SubsribeMWI', '0');		# 0 = disable, 1 = enable
psetting('account|CIDSource', '0');		# 0 = FROM, 1 = PAI
psetting('account|EnableSessionTimmer', '0');	# 0 = disable, 1 = enable
psetting('account|SessionExpires', '');		# 1 to 999
psetting('account|SessionRefresher', '0');	# 0 = UAC, 1 = UAS
psetting('account|EnableUserEqualPhone', '1');	# 0 = disable, 1 = enable
psetting('account|srtp_encryption', '0');	# 0 = disable, 1 = enable
psetting('account|ptime', '0');			# 0 = disable, 10,20,30,40,50 or 60ms
psetting('account|ShareLine', '0');		# 0 = disable, 1 = enable
psetting('account|dialoginfo_callpickup', '0');	# 0 = disable, 1 = enable
psetting('account|AutoAnswer', '0');		# 0 = disable, 1 = enable
psetting('account|MissedCallLog', '0');		# 0 = disable, 1 = enable
psetting('account|AnonymousCall_OnCode', '');
psetting('account|AnonymousCall_OffCode', '');
psetting('account|AnonymousReject_OnCode', '');
psetting('account|AnonymousReject_OffCode', '');
psetting('account|BLANumber', '');
psetting('account|BLASubscribePeriod', '300');	# 60 to 7200, default 300
psetting('account|SubscribeMWIExpire', '3600');	# 0 to 84600, default 3600
psetting('account|CIDSource', '0');		# 0 = FROM, 1 = PAI
psetting('account|RegisterMAC', '1');		# 0 = disable, 1 = enable
psetting('account|RegisterLine', '1');		# 0 = disable, 1 = enable
psetting('account|RegFailRetryInterval', '30');	# 0 to 1800, default 30

# audio0
psetting('audio0|path', '/yealink/config/voip/sipAccount0.cfg');
psetting('audio0|enable', '0');	# 0 = disable, 1 = enable
psetting('audio0|PayloadType', 'PCMU');
psetting('audio0|priority', '1');
psetting('audio0|rtpmap', '0');

# audio1
psetting('audio1|path', '/yealink/config/voip/sipAccount0.cfg');
psetting('audio1|enable', '1');	# 0 = disable, 1 = enable
psetting('audio1|PayloadType', 'PCMA');
psetting('audio1|priority', '2');
psetting('audio1|rtpmap', '8');

# audio2
psetting('audio2|path', '/yealink/config/voip/sipAccount0.cfg');
psetting('audio2|enable', '0');	# 0 = disable, 1 = enable
psetting('audio2|PayloadType', 'G732_53');
psetting('audio2|priority', '3');
psetting('audio2|rtpmap', '4');

# audio3
psetting('audio3|path', '/yealink/config/voip/sipAccount0.cfg');
psetting('audio3|enable', '0');	# 0 = disable, 1 = enable
psetting('audio3|PayloadType', 'G732_63');
psetting('audio3|priority', '4');
psetting('audio3|rtpmap', '4');

# audio4
psetting('audio4|path', '/yealink/config/voip/sipAccount0.cfg');
psetting('audio4|enable', '0');	# 0 = disable, 1 = enable
psetting('audio4|PayloadType', 'G729');
psetting('audio4|priority', '5');
psetting('audio4|rtpmap', '18');

# audio5
psetting('audio5|path', '/yealink/config/voip/sipAccount0.cfg');
psetting('audio5|enable', '0');	# 0 = disable, 1 = enable
psetting('audio5|PayloadType', 'G722');
psetting('audio5|priority', '6');
psetting('audio5|rtpmap', '9');

# audio6
psetting('audio6|path', '/yealink/config/voip/sipAccount0.cfg');
psetting('audio6|enable', '0');
psetting('audio6|PayloadType', 'iLBC');
psetting('audio6|priority', '7');
psetting('audio6|rtpmap', '97');

# audio7
psetting('audio7|path', '/yealink/config/voip/sipAccount0.cfg');
psetting('audio7|enable', '0');	# 0 = disable, 1 = enable
psetting('audio7|PayloadType', 'G726-16');
psetting('audio7|priority', '8');
psetting('audio7|rtpmap', '112');

# audio8
psetting('audio8|path', '/yealink/config/voip/sipAccount0.cfg');
psetting('audio8|enable', '0');	# 0 = disable, 1 = enable
psetting('audio8|PayloadType', 'G726-24');
psetting('audio8|priority', '9');
psetting('audio8|rtpmap', '102');

# audio9
psetting('audio9|path', '/yealink/config/voip/sipAccount0.cfg');
psetting('audio9|enable', '0');	# 0 = disable, 1 = enable
psetting('audio9|PayloadType', 'G726-32');
psetting('audio9|priority', '10');
psetting('audio9|rtpmap', '2');

# audio10
psetting('audio10|path', '/yealink/config/voip/sipAccount0.cfg');
psetting('audio10|enable', '0');	# 0 = disable, 1 = enable
psetting('audio10|PayloadType', 'G726-40');
psetting('audio10|priority', '11');
psetting('audio10|rtpmap', '104');

# DTMF
psetting('DTMF|path', '/yealink/config/voip/sipAccount0.cfg');
psetting('DTMF|DTMFInbandTransfer', '1');	# 0 = INBAND, 1 = RFC2833, 2 = SIP INFO
psetting('DTMF|InfoType', '0');			# 0 = disable, 1 = DTMF-Relay, 2 = DTMF, 3 = Telephone-Event
psetting('DTMF|DTMFPayload', '101');		# 96 to 255

# NAT
psetting('NAT|path', '/yealink/config/voip/sipAccount0.cfg');
psetting('NAT|NATTraversal', '0');	# 0 = disable, 1 = enable
//psetting('NAT|STUNServer', '');
//psetting('NAT|STUNPort', '10000');	# default 10000
//psetting('NAT|EnableUDPUpdate', '1');	# 0 = disable, 1 = enable
//psetting('NAT|UDPUpdateTime', '30');	# in seconds
//psetting('NAT|rport', '0');		# 0 = disable, 1 = enable

# ADVANCED
psetting('ADVANCED|path', '/yealink/config/voip/sipAccount0.cfg');
psetting('ADVANCED|default_t1', '0.5');	# default 0.5
psetting('ADVANCED|default_t2', '4');	# default 4
psetting('ADVANCED|default_t4', '5');	# default 5

# blf
psetting('blf|path', '/yealink/config/voip/sipAccount0.cfg');
psetting('blf|SubscribePeriod', '1800');	# in seconds
psetting('blf|BLFList_URI', '');


#####################################################################
# /yealink/config/voip/sipAccountX.cfg  //FIXME don't function
#####################################################################

/*switch ($phone_model) {
	case 'ip280': $max_sip_accounts = 13; break;
	case 'ip284': $max_sip_accounts = 13; break;
	case 'ip286': $max_sip_accounts = 13; break;
	default:      $max_sip_accounts =  0;
}

for ($i=1; $i<$max_sip_accounts; ++$i) {
	//gs_log(GS_LOG_NOTICE, 'SIP Account '.$i); //FIXME
	psetting('account|path', '/yealink/config/voip/sipAccount'.$i.'.cfg');
	psetting('account|Enable', '0');	# 0 = disable, 1 = enable
}*/




#####################################################################
#  set Keys (only ip284, ip286)
#####################################################################

# DKtype:
#  0 = N/A
#  1 = Conference
#  2 = Forward
#  3 = Transfer
#  4 = Hold
#  5 = DND
#  6 = Redial
#  7 = Call Return
#  8 = SMS
#  9 = Call Pickup
# 10 = Call Park
# 11 = Custom
# 12 = Voicemail
# 13 = Speeddial
# 14 = Intercom
# 15 = Line (for line key only)
# 16 = blf
# 17 = URL
# 18 = Group Listening
# 19 = Public Hold
# 20 = Private Hold
# 27 = XML Browser

if ( in_array($phone_model, array('ip284','ip286'), true) ) {

	# reset Keys on Phone
	$max_keys = 10;
	for ($i=1; $i <= $max_keys; $i++) {
		psetting('memory'.$i.'|path', '/yealink/config/vpPhone/vpPhone.ini');
		psetting('memory'.$i.'|Line', '0');
		psetting('memory'.$i.'|type', '');
		psetting('memory'.$i.'|Value', '');
		psetting('memory'.$i.'|DKtype', '0');
		psetting('memory'.$i.'|PickupValue', '');
	}

	# reset Line Keys on Phone
	for ($i=11; $i <= 16; $i++) {
		psetting('memory'.$i.'|path', '/yealink/config/vpPhone/vpPhone.ini');
		psetting('memory'.$i.'|Line', $i-10);
		psetting('memory'.$i.'|type', '');
		psetting('memory'.$i.'|Value', '');
		psetting('memory'.$i.'|DKtype', '15');
		psetting('memory'.$i.'|PickupValue', '');
	}

	# reset programmable Keys on Phone
	//FIXME
	
	# reset Keys on Expansions Modul ( the correct order 3 2 1)
	for ($j=3; $j >= 1; $j--) {
		for ($i=0; $i <= 37; $i++) {
			psetting('Key'.$i.'|path', '/yealink/config/vpPhone/Ext38_0000000000000'.$j.'.cfg');
			psetting('Key'.$i.'|Line', '0');
			psetting('Key'.$i.'|type', '');
			psetting('Key'.$i.'|Value', '');
			psetting('Key'.$i.'|DKtype', '0');
			psetting('Key'.$i.'|PickupValue', '');
		}
	}


	$softkeys = null;
	$GS_Softkeys = gs_get_key_prov_obj( $phone_type );
	if ($GS_Softkeys->set_user( $user['user'] )) {
		if ($GS_Softkeys->retrieve_keys( $phone_type, array(
			'{GS_PROV_HOST}'      => gs_get_conf('GS_PROV_HOST'),
			'{GS_P_PBX}'          => $pbx,
			'{GS_P_EXTEN}'        => $user_ext,
			'{GS_P_ROUTE_PREFIX}' => $hp_route_prefix,
			'{GS_P_USER}'         => $user['user']
		) )) {
			$softkeys = $GS_Softkeys->get_keys();
		}
	}
	if (! is_array($softkeys)) {
		gs_log( GS_LOG_WARNING, 'Failed to get softkeys' );
	} else {
		foreach ($softkeys as $key_name => $key_defs) {
			if (array_key_exists('slf', $key_defs)) {
				$key_def = $key_defs['slf'];
			} elseif (array_key_exists('inh', $key_defs)) {
				$key_def = $key_defs['inh'];
			} else {
				continue;
			}
			$key_idx = (int)lTrim(subStr($key_name,1),'0');
			if ($key_def['function'] === 'f0') continue;
			//setting('fkey', $key_idx, $key_def['function'] .' '. $key_def['data'], array('context'=>'active'));

			# Keys on Phone
			if ($key_idx >= 1 && $key_idx <= 10 ) {
				psetting('memory'.$key_idx.'|path', '/yealink/config/vpPhone/vpPhone.ini');
				psetting('memory'.$key_idx.'|Line', '0');
				psetting('memory'.$key_idx.'|Value', $key_def['data']);
				psetting('memory'.$key_idx.'|DKtype', subStr($key_def['function'],1));
		
				# for BLF
				if (subStr($key_def['function'],1) == 16 ) {
					psetting('memory'.$key_idx.'|type', 'blf');
					psetting('memory'.$key_idx.'|PickupValue', '*81*'.$key_def['data']);
				}
			}
			
			# Keys on Expansion Modul
			if ($key_idx >= 100 ) {
				$key_tmp = (int)lTrim(subStr($key_idx,1),'0');
				psetting('Key'.$key_tmp.'|path', '/yealink/config/vpPhone/Ext38_0000000000000'.subStr($key_idx,0,1).'.cfg');
				psetting('Key'.$key_tmp.'|Line', '0');
				psetting('Key'.$key_tmp.'|Value', $key_def['data']);
				psetting('Key'.$key_tmp.'|DKtype', subStr($key_def['function'],1));

				# for BLF
				if (subStr($key_def['function'],1) == 16 ) {
					psetting('Key'.$key_tmp.'|type', 'blf');
					psetting('Key'.$key_tmp.'|PickupValue', '*81*'.$key_def['data']);
				}
			}
		}
	}

	# XML Browser for Phonebook on Line Key 2
	psetting('memory12|path', '/yealink/config/vpPhone/vpPhone.ini');
	psetting('memory12|Value', $prov_url_yealink.'pb.php?u='.$user_ext);
	psetting('memory12|DKtype', '27');
	psetting('memory12|PickupValue', __('Tel.buch'));
	psetting('memory12|Label', __('Tel.buch'));

	# XML Browser for Dial-Log on Line Key 3
	psetting('memory13|path', '/yealink/config/vpPhone/vpPhone.ini');
	psetting('memory13|Value', $prov_url_yealink.'dial-log.php?u='.$user_ext);
	psetting('memory13|DKtype', '27');
	psetting('memory13|PickupValue', __('Anruf Listen'));
	psetting('memory13|Label', __('Anruf Listen'));

}

?>