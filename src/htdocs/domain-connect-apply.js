var extensionName = 'DomainConnect';
Jsw.namespace('PleskExt.' + extensionName);
var apply = function (app) {
    var ce = function (tag, attr, children) {
        attr = attr || {};
        children = children || [];
        children = Array.isArray(children) ? children : [children];
        children = children.reduce(function (acc, child) {
            return acc.concat(child);
        }, []).filter(function (child) {
            return !!child;
        });

        var el = document.createElement(tag);
        Object.keys(attr).forEach(function (key) {
            el.setAttribute(key, attr[key]);
        });
        children.forEach(function (child) {
            if (typeof child === 'string') {
                child = document.createTextNode(child);
            }
            el.appendChild(child);
        });
        return el;
    };
    var render = function (renderTo, elements) {
        elements = elements || [];
        elements = Array.isArray(elements) ? elements : [elements];
        elements = elements.filter(function (el) {
            return !!el;
        });

        while (renderTo.firstChild) {
            renderTo.removeChild(renderTo.firstChild);
        }
        elements.forEach(function (el) {
            renderTo.appendChild(el);
        });
    };

    var sanitizeTags = function (text) {
        return text.split(new RegExp('<strong>(.*?)</strong>'))
            .map(function (chunk) {
                var textNode = document.createTextNode(chunk);
                if (0 < text.indexOf('<strong>' + chunk + '</strong>')) {
                    var strong = document.createElement('strong');
                    strong.appendChild(textNode);
                    return strong;
                }
                return textNode;
            });
    };
    var lmsg = function (key, params) {
        params = params || {};
        var str = app.locale[key] || '[' + key + ']';
        Object.keys(params).forEach(function (key) {
            str = str.replace('%%' + key + '%%', params[key]);
        });
        return sanitizeTags(str);
    };

    var displayRecord = function (record) {
        switch (record.type) {
            case 'A':
            case 'AAAA':
            case 'CNAME':
            case 'NS':
                return record.host + ' IN ' + record.type + ' ' + record.pointsTo;
            case 'MX':
                return record.host + ' IN ' + record.type + ' ' + record.pointsTo +
                    (record.priority ? ' (' + record.priority + ')' : '');
            case 'TXT':
                return record.host + ' IN ' + record.type + ' ' + record.data;
            case 'SRV':
                return record.service + '.' + record.protocol + '.' + record.name + ' IN SRV ' +
                    record.target + ' ' + record.priority + ' ' + record.weight + ' ' + record.port;
            default:
                return record.host + ' IN ' + record.type;
        }
    };

    var renderRecordRow = function (record) {
        return ce('li', {}, displayRecord(record));
    };

    render(app.renderTo, [
        app.logoUrl ? ce('p', {}, [
            ce('img', {class: "ext-domain-connect--logo", src: app.logoUrl})
        ]) : null,
        ce('p', {}, lmsg('description', {domain: app.domainName, providerName: app.providerName})),
        ce('p', {}, [
            ce('a', {id: 'ext-domain-connect--details-link'}, lmsg('showDetails'))
        ]),
        ce('div', {id: 'ext-domain-connect--details', class: "hidden"}, [
            app.changes.toRemove.length ? ce('div', {}, [
                ce('div', {}, lmsg('toRemove')),
                ce('ul', {}, app.changes.toRemove.map(renderRecordRow))
            ]) : null,
            app.changes.toAdd.length ? ce('div', {}, [
                ce('div', {}, lmsg('toAdd')),
                ce('ul', {}, app.changes.toAdd.map(renderRecordRow))
            ]) : lmsg('nothingToAdd')
        ]),
        ce('p', {}, lmsg('action', {providerName: app.providerName})),
        ce('div', {class: "btns-box"}, [
            ce('div', {class: "box-area"}, [
                ce('div', {class: "form-row"}, [
                    ce('div', {class: "single-row"}, [
                        ce('button', {id: "ext-domain-connect--submit", class: "btn btn-primary", type: 'button'}, lmsg('connectButton')),
                        ce('button', {id: "ext-domain-connect--cancel", class: "btn", type: 'button'}, lmsg('cancelButton'))
                    ])
                ])
            ])
        ])
    ]);

    var detailsLink = document.getElementById('ext-domain-connect--details-link');
    var details = document.getElementById('ext-domain-connect--details');
    var showDetails = false;
    detailsLink.addEventListener('click', function (event) {
        event.preventDefault();
        showDetails = !showDetails;
        if (showDetails) {
            details.classList.remove('hidden');
            render(detailsLink, lmsg('hideDetails'));
        } else {
            details.classList.add('hidden');
            render(detailsLink, lmsg('showDetails'));
        }
    });

    document.getElementById('ext-domain-connect--submit').addEventListener('click', function () {
        var form = document.createElement('form');
        form.setAttribute('method', 'POST');
        render(document.body, form);
        form.submit();
    });
    document.getElementById('ext-domain-connect--cancel').addEventListener('click', function () {
        if (app.redirectUri) {
            window.location = app.redirectUri +
                (-1 === app.redirectUri.indexOf('?') ? '?' : '&') + 'error=access_denied&error_description=user_cancel';
        } else {
            window.close();
        }
    });
};
PleskExt[extensionName].apply = apply;
