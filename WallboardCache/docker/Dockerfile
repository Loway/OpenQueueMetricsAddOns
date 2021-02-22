# This sucks big time, but I don't care about thin images.
# 
FROM centos:centos7
RUN yum install -y curl  gcc-c++ make atk java-atk-wrapper at-spi2-atk gtk3 libXt
RUN yum update -y nss
RUN curl -sL https://rpm.nodesource.com/setup_10.x | sudo bash -
RUN curl https://intoli.com/install-google-chrome.sh | bash
RUN yum install -y nodejs

CMD /bin/bash

