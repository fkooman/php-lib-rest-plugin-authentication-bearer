%global composer_vendor  fkooman
%global composer_project rest-plugin-bearer

%global github_owner     fkooman
%global github_name      php-lib-rest-plugin-bearer

Name:       php-%{composer_vendor}-%{composer_project}
Version:    0.1.0
Release:    1%{?dist}
Summary:    Bearer Authentication plugin for fkooman/rest

Group:      System Environment/Libraries
License:    ASL 2.0
URL:        https://github.com/%{github_owner}/%{github_name}
Source0:    https://github.com/%{github_owner}/%{github_name}/archive/%{version}.tar.gz
BuildArch:  noarch

Provides:   php-composer(%{composer_vendor}/%{composer_project}) = %{version}

Requires:   php >= 5.4

Requires:   php-composer(guzzlehttp/guzzle) >= 4.0
Requires:   php-composer(guzzlehttp/guzzle) < 5.0
Requires:   php-composer(guzzlehttp/streams) >= 1.0
Requires:   php-composer(guzzlehttp/streams) < 2.0

Requires:   php-composer(fkooman/rest) >= 0.6.2
Requires:   php-composer(fkooman/rest) < 0.7.0
Requires:   php-composer(fkooman/oauth-common) >= 0.6.1
Requires:   php-composer(fkooman/oauth-common) < 0.7.0

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
%dir %{_datadir}/php/%{composer_vendor}/Rest/Plugin/Bearer
%{_datadir}/php/%{composer_vendor}/Rest/Plugin/Bearer/*
%doc README.md CHANGES.md COPYING composer.json

%changelog
* Wed Oct 22 2014 François Kooman <fkooman@tuxed.net> - 0.1.0-1
- initial package
