package main

import (
	"encoding/json"
	"errors"
	"fmt"
	"log"
	"net/http"
	"regexp"
)

const listenPort = "8080"
const providerId = "plesk"
const providerName = "Plesk"
const providerDisplayName = "Domain powered by Plesk"
const windowWidth = 750
const windowHeight = 750

type response struct {
	ProviderId          string `json:"providerId"`
	ProviderName        string `json:"providerName"`
	ProviderDisplayName string `json:"providerDisplayName"`
	UrlSyncUX           string `json:"urlSyncUX"`
	UrlAPI              string `json:"urlAPI"`
	Width               int    `json:"width"`
	Height              int    `json:"height"`
}

var reUrl *regexp.Regexp

func init() {
	reUrl = regexp.MustCompile(`(?:/host/(?P<host>.*?))?(?:/port/(?P<port>\d*?))?/v2/(?P<domain>.*?)/settings(/|$)`)
}

func main() {
	log.Printf("Start server listening on %s port...", listenPort)
	http.HandleFunc("/", handleEntryPoint)

	log.Fatal(http.ListenAndServe(fmt.Sprintf(":%s", listenPort), nil))
}

func handleEntryPoint(w http.ResponseWriter, r *http.Request) {
	url := r.URL.Path
	host, port, err := parseUrl(url)
	if err != nil {
		w.WriteHeader(http.StatusBadRequest)
		log.Printf("Failed to parse URL '%s': %s", url, err)
		return
	}

	entryPointUrl := fmt.Sprintf(`https://%v:%v/modules/domain-connect/index.php/`, host, port)
	res := response{
		ProviderId:          providerId,
		ProviderName:        providerName,
		ProviderDisplayName: providerDisplayName,
		UrlSyncUX:           entryPointUrl,
		UrlAPI:              entryPointUrl,
		Width:               windowWidth,
		Height:              windowHeight,
	}

	output, err := json.Marshal(res)
	if err != nil {
		w.WriteHeader(http.StatusServiceUnavailable)
		log.Printf("Failed json encoding: %s", err)
	} else {
		w.Header().Set("Content-Type", "application/json; charset=UTF-8")
		w.WriteHeader(http.StatusOK)
		w.Write(output)
	}
}

func parseUrl(url string) (string, string, error) {
	matches := reUrl.FindStringSubmatch(url)
	if len(matches) == 0 {
		return "", "", errors.New("invalid path")
	}
	host := matches[1]
	port := matches[2]
	domain := matches[3]
	if host == "" {
		host = domain
	}
	if port == "" {
		port = "8443"
	}
	return host, port, nil
}
