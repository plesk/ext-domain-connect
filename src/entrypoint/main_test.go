package main

import (
	"bytes"
	"encoding/json"
	"net/http"
	"net/http/httptest"
	"testing"
	"strings"
)

func TestEntryPoint(t *testing.T) {
	req, _ := http.NewRequest("GET", "/host/plesk.com/port/8443/v2/example.com/settings", nil)
	res := executeRequest(req)

	checkResponseCode(t, res.Code, http.StatusOK)
	checkResponseContentType(t, res.HeaderMap, "application/json; charset=UTF-8")
	checkBody(t, res.Body, response{
		ProviderId:          providerId,
		ProviderName:        providerName,
		ProviderDisplayName: providerDisplayName,
		UrlSyncUX:           "https://plesk.com:8443/modules/domain-connect/index.php/",
		UrlAPI:              "https://plesk.com:8443/modules/domain-connect/index.php/",
		Width:               windowWidth,
		Height:              windowHeight,
	})
}

func TestWithoutPort(t *testing.T) {
	req, _ := http.NewRequest("GET", "/host/plesk.com/v2/example.com/settings", nil)
	res := executeRequest(req)

	checkResponseCode(t, res.Code, http.StatusOK)
	checkBody(t, res.Body, response{
		ProviderId:          providerId,
		ProviderName:        providerName,
		ProviderDisplayName: providerDisplayName,
		UrlSyncUX:           "https://plesk.com:8443/modules/domain-connect/index.php/",
		UrlAPI:              "https://plesk.com:8443/modules/domain-connect/index.php/",
		Width:               windowWidth,
		Height:              windowHeight,
	})
}

func TestWithoutHost(t *testing.T) {
	req, _ := http.NewRequest("GET", "/v2/example.com/settings", nil)
	res := executeRequest(req)

	checkResponseCode(t, res.Code, http.StatusOK)
	checkBody(t, res.Body, response{
		ProviderId:          providerId,
		ProviderName:        providerName,
		ProviderDisplayName: providerDisplayName,
		UrlSyncUX:           "https://example.com:8443/modules/domain-connect/index.php/",
		UrlAPI:              "https://example.com:8443/modules/domain-connect/index.php/",
		Width:               windowWidth,
		Height:              windowHeight,
	})
}

func TestBadRequest(t *testing.T) {
	req, _ := http.NewRequest("GET", "/bad", nil)
	res := executeRequest(req)

	checkResponseCode(t, res.Code, http.StatusBadRequest)
}

func executeRequest(req *http.Request) *httptest.ResponseRecorder {
	rr := httptest.NewRecorder()
	handleEntryPoint(rr, req)

	return rr
}

func checkResponseCode(t *testing.T, actual, expected int) {
	if expected != actual {
		t.Errorf("Expected response code %d. Got %d\n", expected, actual)
	}
}

func checkResponseContentType(t *testing.T, actual http.Header, expected string) {
	if strings.Join(actual["Content-Type"], "\n") != expected {
		t.Errorf("Expected the resonse %v. Got %v", expected, actual["Content-Type"])
	}
}

func checkBody(t *testing.T, actual *bytes.Buffer, expected response) {
	body := actual.Bytes()
	var res response
	if err := json.Unmarshal(body, &res); err != nil {
		t.Errorf("Invalid json body: %s", err)
	}
	if res != expected {
		t.Errorf("Expected the resonse %v. Got %v", expected, res)
	}
}
