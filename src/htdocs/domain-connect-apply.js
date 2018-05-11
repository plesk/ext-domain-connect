(function (app) {
    var ce = function (tag, attr, children) {
        attr = attr || {};
        children = children || [];
        children = Array.isArray(children) ? children : [children];
        children = children.reduce(function (acc, child) {
            return acc.concat(child);
        }, []);

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

        while (renderTo.firstChild) {
            renderTo.removeChild(renderTo.firstChild);
        }
        elements.forEach(function (child) {
            renderTo.appendChild(child);
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
                return record.host + ' IN ' + record.type + ' ' + record.pointsTo;
            case 'TXT':
                return record.host + ' IN ' + record.type + ' ' + record.data;
            default:
                return record.host + ' IN ' + record.type;
        }
    };

    var renderRecordRow = function (record) {
        return ce('li', {}, displayRecord(record));
    };

    render(app.renderTo, [
        ce('p', {}, [
            ce('img', {class: "ext-domain-connect--logo", src: app.logoUrl})
        ]),
        ce('p', {}, lmsg('description', {domain: app.domainName, providerName: app.providerName})),
        ce('p', {}, [
            ce('a', {id: 'ext-domain-connect--details-link'}, lmsg('showDetails'))
        ]),
        ce('div', {id: 'ext-domain-connect--details', class: "hidden"}, [
            ce('div', {}, [
                ce('div', {}, lmsg('toRemove')),
                ce('ul', {}, app.changes.toRemove.map(renderRecordRow))
            ]),
            ce('div', {}, [
                ce('div', {}, lmsg('toAdd')),
                ce('ul', {}, app.changes.toAdd.map(renderRecordRow))
            ])
        ]),
        ce('p', {}, lmsg('action', {providerName: app.providerName})),
        ce('div', {class: "btns-box"}, [
            ce('div', {class: "box-area"}, [
                ce('div', {class: "form-row"}, [
                    ce('div', {class: "single-row"}, [
                        ce('button', {class: "btn btn-primary", type: 'button'}, lmsg('connectButton')),
                        ce('button', {class: "btn", type: 'button'}, lmsg('cancelButton'))
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
})(PleskExt.DomainConnect);
