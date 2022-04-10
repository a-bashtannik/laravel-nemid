# ⚠️ MitID is replacing NemID

Throughout 2021 and 2022, MitID will replace NemID. All NemID users will be notified by their online or mobile bank when it is their turn to get MitID. Those who do not have online banking will be notified via Digital Post.

In the future, gambling operators (private service providers) must, among other things, use a broker who will be responsible for the authentication process of the player in order to be connected to MitID. You can read more about the new requirement on the Agency for Digitisation’s website. Here you will find a site with more information for private service providers (gambling operators) on how to deal with the transition from NemID to MitID.

This package is now archived and won't be updated anymore.

# NemID laravel package

NemID is Denmark’s security solution for log-in and signing on the Internet.

A typical use of NemID is that an end user wishes to log on to a service provider. This is done by the user opening the web page of the service provider
and choosing a “Log on” functionality. The service provider will thereby initiate that the end user is authenticated. The result of the authentication is then
communicated to the service provider, and based on the result of the authentication the service provider can either choose to reject the end user or to
present the end user with a personalised home page.

The [NemID Service Provider Package](https://www.nets.eu/dk-da/kundeservice/nemid-tjenesteudbyder/The-NemID-service-provider-package) contains the necessary documentation and codes in order for you to implement and test NemID on your site, both in regards to NemID for private and NemID for employees.

This Laravel Package provides access to the follwing NemID features:

- Login using JavaScript applet (login/password and OTP device)
- Login using CodeFile applet (installed certificates)

# Installing

Install PHP extensions:

```shell
sudo apt install libgmp-dev php-gmp php-intl 
```

Install fixed dependencies manually from the private repository — `sop/*` crypto libraries were a bit outdated and have been upgraded by [Andrew Bashtannik](mailto://bashtannik@gmail.com).

> X509 is a PHP-8 / PHP-7 versions of the same master release, composer.json was updated
> 
> ASN1 is a PHP-8 / PHP-7 versions that contain fixed unicode VisibleString type problem.
> See [commit](https://github.com/a-bashtannik/asn1/commit/ef1ab9e68247e2fd26f85455b0a5732de2cab5b1).

```json
  {
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/a-bashtannik/x509.git"
    },
    {
      "type": "vcs",
      "url": "https://github.com/a-bashtannik/asn1.git"
    }
  ]
}
```

Install manually the following packages:

### PHP-8

```json
{
  "require": {
    "sop/asn1": "dev-nemid as 4.1.0",
    "sop/x509": "dev-php80",
    "rentdesk/nemid": "dev-master"
  }
}
```

### PHP-7.4 - [php74 branch](https://gitlab.com/rentdesk/nemid/-/tree/php74)

```json
{
  "require": {
    "sop/asn1": "dev-nemid-php70 as 3.4.3",
    "sop/x509": "dev-php70",
    "rentdesk/nemid": "dev-php74"
  }
}
```

Make sure you have no `config/nemid.php` file or backup it.

Publish configuration and vue component if need:

```shell
php artisan vendor:publish --provider="Rentdesk\Nemid\NemidServiceProvider" --tag="config"
php artisan vendor:publish --provider="Rentdesk\Nemid\NemidServiceProvider" --tag="vue-components"
```

Update `config/nemid.php` with correct routes for the certificates and keys.

# Using the package

> The package is shipped with 2 routes: `nemid/javascript` and `nemid/codefile`.

1. HTTP GET `nemid/javascript` 
1. Store the response. See `\Rentdesk\Nemid\Services\JavaScriptClient::getLoginConfig` and NemID integration documentation.
1. Initialize the iframes. See `resources/js/vue-nemid` and `resources/views/debug.blade.php` for examples.
1. Send the config from the step 2 to the iframe content window.
1. Obtain the response and send base64-encoded string to the `\Rentdesk\Nemid\Services\NemidService::parseResponse` method.

Process the data, see `\Rentdesk\Nemid\Models\XMLDSig` class for available methods.

# Debugging

NemID is behind a firewall. You need an IP address that is allowed to access the NemID service.

If the app is in debug mode (`app.debug = true`), run

```shell
 ./vendor/bin/testbench serve --port=8888   
```

See debug tool on http://127.0.0.1:8888/nemid/debug

> Create a fake person here: https://appletk.danid.dk/developers/ for POCES (personal) certificates tests.

> Use `tests/fixtures/MOCES-*` certificates for MOCES (employee on behalf of a company) certificates tests.

> For documentation see `/doc` folder.

### Frontend

The package contains Vue 2 component located in `resources/js/vue-nemid/src/components`.

Install dependencies:

```
npm install
```

Compiles and hot-reloads for development

```
npm run serve
```

See [Configuration Reference](https://cli.vuejs.org/config/).

By default, the app proxies any requests to http://127.0.0.1:8888 (see `vue.config.js`).

# CA Certificates

## TRUST2048 Production Certificates

Download production CAs here:

https://www.nets.eu/dk-da/kundeservice/nemid-tjenesteudbyder/The-NemID-service-provider-package/Pages/news.aspx

See `/certificates` directory for currently available PCA and CA certificates.

## TRUST2408 Systemtest Certificates

1. Generate a fake person on https://appletk.danid.dk/developers/
1. Download generated certificate (on the bottom of the page)
1. Verify it's a DER certificate named `XXXXXX.crt`
1. Run `openssl x509 -in 385400029_857080.crt -text -inform DER -noout | grep -i "issuer"`
1. Download the issuing CA on the output link, for example:

```shell
Issuer: C = DK, O = TRUST2408, CN = TRUST2408 Systemtest XXXIV CA
        CA Issuers - URI:http://aia.systemtest34.trust2408.com/systemtest34-ca.cer
```

It's the fresh issuing test CA certificate you can use for verifying the latest client certificates.

Verify it's still issued by TRUST2408 Systemtest VII Primary CA (notice, it's PEM certificate, not DER).

```shell
openssl x509 -in TRUST2408-Systemtest-XXXIV-CA.cer -text  -inform PEM -noout | grep -i "issuer"
```

## CRL

Download CRLs and setup paths in the `config.php` file

```shell
wget http://crl.ica05.trust2408.com/ica05.crl
wget http://crl.ica04.trust2408.com/ica04.crl
wget http://crl.ica03.trust2408.com/ica03.crl
wget http://crl.ica02.trust2408.com/ica02.crl
wget http://crl.oces-issuing01.trust2408.com/ica011.crl

# Test CRLs

wget http://crl.systemtest8.trust2408.com/systemtest8.crl
wget http://crl.systemtest34.trust2408.com/systemtest34.crl
wget http://crl.systemtest22.trust2408.com/systemtest221.crl
```

Crontab stub:

```
0 0 * * * wget -q http://crl.ica05.trust2408.com/ica05.crl --output-document=/var/www/html/app/shared/storage/app/crl/ica05.crl
0 0 * * * wget -q http://crl.ica05.trust2408.com/ica04.crl --output-document=/var/www/html/app/shared/storage/app/crl/ica04.crl
0 0 * * * wget -q http://crl.ica05.trust2408.com/ica03.crl --output-document=/var/www/html/app/shared/storage/app/crl/ica03.crl
0 0 * * * wget -q http://crl.ica05.trust2408.com/ica02.crl --output-document=/var/www/html/app/shared/storage/app/crl/ica02.crl
0 0 * * * wget -q http://crl.oces-issuing01.trust2408.com/ica011.crl --output-document=/var/www/html/app/shared/storage/app/crl/ica011.crl

0 0 * * * wget -q http://crl.systemtest22.trust2408.com/systemtest8.crl --output-document=/home/deployer/app/storage/app/crl/systemtest8.crl
0 0 * * * wget -q http://crl.systemtest22.trust2408.com/systemtest34.crl --output-document=/home/deployer/app/storage/app/crl/systemtest34.crl
0 0 * * * wget -q http://crl.systemtest22.trust2408.com/systemtest221.crl --output-document=/home/deployer/app/storage/app/crl/systemtest221.crl  
```

# PID-CPR and RID-CPR Webservices

Nets.eu prodivdes 2 webservices:

> See https://www.nets.eu/dk-da/kundeservice/nemid-tjenesteudbyder/NemID-tjenesteudbyderpakken/Documents/Configuration%20and%20setup.pdf

#### PID-CPR

- SOAP https://pidws.pp.certifikat.dk/pid_serviceprovider_server/pidws/
- XML https://pidws.pp.certifikat.dk/pid_serviceprovider_server/pidxml/

#### RID-CPR

- SOAP https://ws-erhverv.pp.certifikat.dk/rid_serviceprovider_server/services/HandleSundhedsportalWSPort

#### Using webservices

1. Receive user's certificate using JavaScript or CodeFile applet.
1. Extract from the certificate a PID (for POCES) or a serialNumber (for MOCES certificate, see `\Rentdesk\Nemid\Models\OCESCertificate::getNemidSerialNumber`)
1. Ask user for CPR (secret Personal Identifier)
    1. For `POCES` certificate call `\Rentdesk\Nemid\Services\NemidWebservice::checkPid` 
    1. For `MOCES` certificate call `\Rentdesk\Nemid\Services\NemidWebservice::checkRid` 

# OpenSSL Cheatsheet

Convert a DER file (.crt .cer .der) to PEM

```shell
openssl x509 -inform der -in certificate.cer -out certificate.pem
```

Convert a PEM file to DER

```shell
openssl x509 -outform der -in certificate.pem -out certificate.der
```

----

Convert a PKCS#12 file (.pfx .p12) containing a private key and certificates to PEM

```shell
openssl pkcs12 -in keyStore.pfx -out keyStore.pem -nodes
```

You can add `-nocerts` to only output the private key or add `-nokeys` to only output the certificates.

Convert a PEM certificate file and a private key to PKCS#12 (.pfx .p12)

```shell
openssl pkcs12 -export -out certificate.pfx -inkey privateKey.key -in certificate.crt -certfile CACert.crt
```

----

Extract Issuer certificate

```shell
openssl x509 -in cert.x509 -text
```

Then find an issuer URL:

```
Authority Information Access: 
OCSP - URI:http://ocsp.systemtest8.trust2408.com/responder
CA Issuers - URI:http://m.aia.systemtest8.trust2408.com/systemtest8-ca.cer

```

Download http://m.aia.systemtest8.trust2408.com/systemtest8-ca.cer - it's a DER certificate

----

To view the contents of a PEM-encoded CRL file, using OpenSSL:

```shell
openssl crl -noout -text -in example.crl
```

To view the contents of a DER-encoded CRL file:

```shell
openssl crl -inform DER -noout -text -in example.crl
```
