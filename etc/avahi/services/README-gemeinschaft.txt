Gemeinschaft
$Revision$

The service definitions for Avahi must go into /etc/avahi/services/ as
regular files, not symlinks. Avahi chroots before reading them! If you
want to use symlinks anyway you need to edit /etc/init.d/avahi-daemon
to start avahi-daemon with the --no-chroot option.

