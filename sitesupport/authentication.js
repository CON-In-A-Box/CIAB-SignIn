/*
 * Base function to check authentication of a user
 */

/* jshint browser: true */
/* jshint -W097 */
/* global basicBackendRequest */
/* exported checkAuthentication */

'use strict';

var onSuccess = null;
var onFail = null;
var user = null;

function _hideAuthentication() {
  document.getElementById('reauthentication_dlg').style.display = 'none';
}

function _cancelAuthentication() {
  _hideAuthentication();
  if (onFail) {
    onFail();
  }
}

function _checkKey(event) {
  if (event.keyCode === 13) {
    _hideAuthentication();
    var pass = document.getElementById('password_input').value;
    var target = document.getElementById('target').value;
    if (!target) {
      target = 'functions';
    }
    basicBackendRequest('POST', target,
      'validate_login=' + user + '&validate_passwd=' + encodeURI(pass),
      function() {
        if (onSuccess) {
          onSuccess();
        }
        return;
      },
      function(response) {
        if (onFail) {
          onFail(response.status);
        }
        return;
      });
  }
}

function checkAuthentication(username, success, failure, params) {
  var dlg = document.getElementById('reauthentication_dlg');
  onSuccess = success;
  onFail = failure;
  user = username;
  if (!dlg) {
    dlg = document.createElement('DIV');
    dlg.classList.add('UI-modal');
    dlg.id = 'reauthentication_dlg';
    var content = document.createElement('DIV');
    dlg.appendChild(content);
    content.classList.add('UI-modal-content');

    var x = document.createElement('SPAN');
    x.onclick = _cancelAuthentication;
    x.classList.add('UI-close-button');
    x.innerHTML = '&times;';
    content.appendChild(x);

    var h = document.createElement('H2');
    h.classList.add('UI-red');
    h.classList.add('UI-center');
    var t;
    if (params && ('title' in params)) {
      t = document.createTextNode(params.title);
    } else {
      t = document.createTextNode('Entering Admin Mode');
    }
    h.appendChild(t);
    content.appendChild(h);
    var r = document.createElement('HR');
    content.appendChild(r);

    h = document.createElement('H3');
    h.classList.add('UI-center');
    t = document.createTextNode('Verify password for ' + username);
    h.appendChild(t);
    content.appendChild(h);

    var form = document.createElement('DIV');
    form.classList.add('UI-center');
    content.appendChild(form);

    var i = document.createElement('INPUT');
    i.classList.add('UI-center');
    i.classList.add('UI-input');
    i.classList.add('UI-margin');
    i.classList.add('UI-padding');
    i.setAttribute('type', 'password');
    i.id = 'password_input';
    i.style.width = '95%';
    i.onkeyup = _checkKey;
    form.appendChild(i);

    i = document.createElement('INPUT');
    i.classList.add('UI-hide');
    i.id = 'target';
    i.value = null;
    if (params && ('target' in params)) {
      i.value = params.target;
    }
    form.appendChild(i);

    r = document.createElement('HR');
    content.appendChild(r);

    document.body.appendChild(dlg);
  } else {
    document.getElementById('password_input').value = '';
  }

  dlg.style.display = 'block';
}
