(function (app) {
    var ce = function (tag, attr, children) {
        attr = attr || {};
        children = children || [];

        var el = new Element(tag, attr);
        children.forEach(function (child) {
            if (typeof child === 'string') {
                child = document.createTextNode(child);
            }
            el.appendChild(child);
        });
        return el;
    };
    var render = function (el) {
        app.renderTo.appendChild(el);
    };

    var lmsg = function (key, params) {
        params = params || {};
        var str = app.locale[key] || '[' + key + ']';
        Object.keys(params).forEach(function (key) {
            str = str.replace('%%' + key + '%%', params[key]);
        });
        return str;
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
        return ce('li', {}, [
            displayRecord(record)
        ]);
    };

    [
        ce('p', {}, [
            ce('img', {class: "ext-domain-connect--logo", src: app.logoUrl})
        ]),
        ce('p', {}, [lmsg('description', {domain: app.domainName, providerName: app.providerName})]),
        ce('p', {}, [
            ce('a', {id: 'ext-domain-connect--details-link'})
        ]),
        ce('div', {id: 'ext-domain-connect--details'}, [
            ce('div', {}, [
                ce('div', {}, [lmsg('toRemove')]),
                ce('ul', {}, app.changes.toRemove.map(renderRecordRow))
            ]),
            ce('div', {}, [
                ce('div', {}, [lmsg('toAdd')]),
                ce('ul', {}, app.changes.toAdd.map(renderRecordRow))
            ])
        ]),
        ce('p', {}, [lmsg('action', {providerName: app.providerName})]),
        ce('div', {class: "btns-box"}, [
            ce('div', {class: "box-area"}, [
                ce('div', {class: "form-row"}, [
                    ce('div', {class: "single-row"}, [
                        ce('button', {class: "btn btn-primary", type: 'button'}, [lmsg('connectButton')]),
                        ce('button', {class: "btn", type: 'button'}, [lmsg('cancelButton')])
                    ])
                ])
            ])
        ])
    ].forEach(render);

    var detailsLink = document.getElementById('ext-domain-connect--details-link');
    var details = document.getElementById('ext-domain-connect--details');
    var showDetails = true;
    var toggleDetails = function (event) {
        if (event) {
            event.preventDefault();
        }
        showDetails = !showDetails;
        if (showDetails) {
            details.classList.remove('hidden');
            detailsLink.innerText = lmsg('hideDetails');
        } else {
            details.classList.add('hidden');
            detailsLink.innerText = lmsg('showDetails');
        }
    };
    toggleDetails();
    detailsLink.addEventListener('click', toggleDetails);
})(PleskExt.DomainConnect);
