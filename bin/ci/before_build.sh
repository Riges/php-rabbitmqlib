#!/bin/sh
sudo rabbitmqctl add_vhost phprabbitmqlib_testbed
sudo rabbitmqctl add_user phprabbitmqlib phprabbitmqlib_password
sudo rabbitmqctl set_permissions -p phprabbitmqlib_testbed phprabbitmqlib ".*" ".*" ".*"