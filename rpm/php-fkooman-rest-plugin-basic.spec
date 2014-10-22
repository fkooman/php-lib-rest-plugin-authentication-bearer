%global composer_vendor  fkooman
%global composer_project rest-plugin-basic

%global github_owner     fkooman
%global github_name      php-lib-rest-plugin-basic

Name:       php-%{composer_vendor}-%{composer_project}
Version:    0.2.1
Release:    3%{?dist}
Summary:    Basic Authentication plugin for fkooman/rest

Group:      System Environment/Libraries
License:    ASL 2.0
URL:        https://github.com/%{github_owner}/%{github_name}
Source0:    https://github.com/%{github_owner}/%{github_name}/archive/%{version}.tar.gz
BuildArch:  noarch

Provides:   php-composer(%{composer_vendor}/%{composer_project}) = %{version}

Requires:   php >= 5.4

Requires:   php-password-compat >= 1.0.0
Requires:   php-composer(fkooman/rest) >= 0.6.0
Requires:   php-composer(fkooman/rest) < 0.7.0

%description
Library written in PHP to make it easy to develop REST applications.

%prep
%setup -qn %{github_name}-%{version}

%build

%install
mkdir -p ${RPM_BUILD_ROOT}%{_datadir}/php
cp -pr src/* ${RPM_BUILD_ROOT}%{_datadir}/php

%files
%defattr(-,root,root,-)
%dir %{_datadir}/php/%{composer_vendor}/Rest/Plugin/Basic
%{_datadir}/php/%{composer_vendor}/Rest/Plugin/Basic/*
%doc README.md CHANGES.md COPYING composer.json

%changelog
* Tue Oct 21 2014 François Kooman <fkooman@tuxed.net> - 0.2.1-3
- require PHP >= 5.4

* Tue Oct 21 2014 François Kooman <fkooman@tuxed.net> - 0.2.1-2
- require php-password-compat

* Tue Oct 21 2014 François Kooman <fkooman@tuxed.net> - 0.2.1-1
- initial package
