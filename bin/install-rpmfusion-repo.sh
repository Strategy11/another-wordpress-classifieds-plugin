#!/bin/bash
if ! ( rpm --quiet -q rpmfusion-free-release-20 ); then
  wget http://download1.rpmfusion.org/free/fedora/rpmfusion-free-release-20.noarch.rpm
  yum install rpmfusion-free-release-20.noarch.rpm
fi

if ! ( rpm --quiet -q rpmfusion-nonfree-release-20 ); then
  wget http://download1.rpmfusion.org/nonfree/fedora/rpmfusion-nonfree-release-20.noarch.rpm
  yum install rpmfusion-nonfree-release-20.noarch.rpm
fi
