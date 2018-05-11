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
    app.renderTo.appendChild(
        ce('div', {class: "btns-box"}, [
            ce('div', {class: "box-area"}, [
                ce('div', {}, [app.locale.description]),
                ce('div', {}, [
                    ce('div', {}, [app.locale.toRemove]),
                    ce('ul', {}, app.changes.toRemove.map(renderRecordRow))
                ]),
                ce('div', {}, [
                    ce('div', {}, [app.locale.toAdd]),
                    ce('ul', {}, app.changes.toAdd.map(renderRecordRow))
                ]),
                ce('div', {}, [app.locale.action]),
                ce('div', {class: "form-row"}, [
                    ce('div', {class: "single-row"}, [
                        ce('button', {class: "btn btn-primary", type: 'button'}, [
                            app.locale.connectButton
                        ]),
                        ce('button', {class: "btn", type: 'button'}, [
                            app.locale.cancelButton
                        ])
                    ])
                ])
            ])
        ])
    );
})(PleskExt.DomainConnect);
