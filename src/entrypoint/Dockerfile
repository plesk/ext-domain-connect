FROM golang:latest
WORKDIR /root/
COPY *.go ./
RUN CGO_ENABLED=0 GOOS=linux go build -a -installsuffix cgo -o app .

FROM scratch
COPY --from=0 /root/app .
CMD ["./app"]
EXPOSE 8080
