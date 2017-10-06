/*
 *
 *   OpenLayers.js -- OpenLayers Map Viewer Library
 *
 *   Copyright (c) 2006-2014 by OpenLayers Contributors
 *   Published under the 2-clause BSD license.
 *   See http://openlayers.org/dev/license.txt for the full text of the license, and http://openlayers.org/dev/authors.txt for full list of contributors.
 *
 *   (For uncompressed versions of the code used, please see the
 *   OpenLayers Github repository: <https://github.com/openlayers/openlayers>)
 *
 *   Includes compressed code under the following licenses:
 *
 * --------------------------------
 *   Contains XMLHttpRequest.js <http://code.google.com/p/xmlhttprequest/>
 *   Copyright 2007 Sergey Ilinsky (http://www.ilinsky.com)
 *
 *   Licensed under the Apache License, Version 2.0 (the "License");
 *   you may not use this file except in compliance with the License.
 *   You may obtain a copy of the License at
 *   http://www.apache.org/licenses/LICENSE-2.0
 * --------------------------------
 *
 * --------------------------------
 *   OpenLayers.Util.pagePosition is based on Yahoo's getXY method, which is
 *   Copyright (c) 2006, Yahoo! Inc.
 *   All rights reserved.
 *
 *   Redistribution and use of this software in source and binary forms, with or
 *   without modification, are permitted provided that the following conditions
 *   are met:
 *
 *    Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *
 *    Redistributions in binary form must reproduce the above copyright notice,
 *     this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution.
 *
 *    Neither the name of Yahoo! Inc. nor the names of its contributors may be
 *     used to endorse or promote products derived from this software without
 *     specific prior written permission of Yahoo! Inc.
 *
 *   THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 *   AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 *   IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 *   ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 *   LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 *   CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 *   SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 *   INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 *   CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *   ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *   POSSIBILITY OF SUCH DAMAGE.
 *   --------------------------------
 */
var OpenLayers = {
  VERSION_NUMBER: 'Release 2.13.1',
  singleFile: true,
  _getScriptLocation: (function () {
    var f = new RegExp('(^|(.*?\\/))(OpenLayers[^\\/]*?\\.js)(\\?|$)'),
    e = document.getElementsByTagName('script'),
    g,
    b,
    c = '';
    for (var d = 0, a = e.length; d < a; d++) {
      g = e[d].getAttribute('src');
      if (g) {
        b = g.match(f);
        if (b) {
          c = b[1];
          break
        }
      }
    }
    return (function () {
      return c
    })
  }) (),
  ImgPath: ''
};
OpenLayers.Class = function () {
  var a = arguments.length;
  var d = arguments[0];
  var c = arguments[a - 1];
  var e = typeof c.initialize == 'function' ? c.initialize : function () {
    d.prototype.initialize.apply(this, arguments)
  };
  if (a > 1) {
    var b = [
      e,
      d
    ].concat(Array.prototype.slice.call(arguments).slice(1, a - 1), c);
    OpenLayers.inherit.apply(null, b)
  } else {
    e.prototype = c
  }
  return e
};
OpenLayers.inherit = function (f, d) {
  var c = function () {
  };
  c.prototype = d.prototype;
  f.prototype = new c;
  var b,
  a,
  e;
  for (b = 2, a = arguments.length; b < a; b++) {
    e = arguments[b];
    if (typeof e === 'function') {
      e = e.prototype
    }
    OpenLayers.Util.extend(f.prototype, e)
  }
};
OpenLayers.Util = OpenLayers.Util || {
};
OpenLayers.Util.extend = function (a, e) {
  a = a || {
  };
  if (e) {
    for (var d in e) {
      var c = e[d];
      if (c !== undefined) {
        a[d] = c
      }
    }
    var b = typeof window.Event == 'function' && e instanceof window.Event;
    if (!b && e.hasOwnProperty && e.hasOwnProperty('toString')) {
      a.toString = e.toString
    }
  }
  return a
};
OpenLayers.Util = OpenLayers.Util || {
};
OpenLayers.Util.getElement = function () {
  var d = [
  ];
  for (var c = 0, a = arguments.length; c < a; c++) {
    var b = arguments[c];
    if (typeof b == 'string') {
      b = document.getElementById(b)
    }
    if (arguments.length == 1) {
      return b
    }
    d.push(b)
  }
  return d
};
OpenLayers.Util.isElement = function (a) {
  return !!(a && a.nodeType === 1)
};
OpenLayers.Util.isArray = function (b) {
  return (Object.prototype.toString.call(b) === '[object Array]')
};
OpenLayers.Util.removeItem = function (c, b) {
  for (var a = c.length - 1; a >= 0; a--) {
    if (c[a] == b) {
      c.splice(a, 1)
    }
  }
  return c
};
OpenLayers.Util.indexOf = function (d, c) {
  if (typeof d.indexOf == 'function') {
    return d.indexOf(c)
  } else {
    for (var b = 0, a = d.length; b < a; b++) {
      if (d[b] == c) {
        return b
      }
    }
    return - 1
  }
};
OpenLayers.Util.dotless = /\./g;
OpenLayers.Util.modifyDOMElement = function (e, h, d, f, a, c, g, b) {
  if (h) {
    e.id = h.replace(OpenLayers.Util.dotless, '_')
  }
  if (d) {
    e.style.left = d.x + 'px';
    e.style.top = d.y + 'px'
  }
  if (f) {
    e.style.width = f.w + 'px';
    e.style.height = f.h + 'px'
  }
  if (a) {
    e.style.position = a
  }
  if (c) {
    e.style.border = c
  }
  if (g) {
    e.style.overflow = g
  }
  if (parseFloat(b) >= 0 && parseFloat(b) < 1) {
    e.style.filter = 'alpha(opacity=' + (b * 100) + ')';
    e.style.opacity = b
  } else {
    if (parseFloat(b) == 1) {
      e.style.filter = '';
      e.style.opacity = ''
    }
  }
};
OpenLayers.Util.createDiv = function (a, i, h, f, e, c, b, g) {
  var d = document.createElement('div');
  if (f) {
    d.style.backgroundImage = 'url(' + f + ')'
  }
  if (!a) {
    a = OpenLayers.Util.createUniqueID('OpenLayersDiv')
  }
  if (!e) {
    e = 'absolute'
  }
  OpenLayers.Util.modifyDOMElement(d, a, i, h, e, c, b, g);
  return d
};
OpenLayers.Util.createImage = function (a, i, h, e, d, c, f, j) {
  var b = document.createElement('img');
  if (!a) {
    a = OpenLayers.Util.createUniqueID('OpenLayersDiv')
  }
  if (!d) {
    d = 'relative'
  }
  OpenLayers.Util.modifyDOMElement(b, a, i, h, d, c, null, f);
  if (j) {
    b.style.display = 'none';
    function g() {
      b.style.display = '';
      OpenLayers.Event.stopObservingElement(b)
    }
    OpenLayers.Event.observe(b, 'load', g);
    OpenLayers.Event.observe(b, 'error', g)
  }
  b.style.alt = a;
  b.galleryImg = 'no';
  if (e) {
    b.src = e
  }
  return b
};
OpenLayers.IMAGE_RELOAD_ATTEMPTS = 0;
OpenLayers.Util.alphaHackNeeded = null;
OpenLayers.Util.alphaHack = function () {
  if (OpenLayers.Util.alphaHackNeeded == null) {
    var d = navigator.appVersion.split('MSIE');
    var a = parseFloat(d[1]);
    var b = false;
    try {
      b = !!(document.body.filters)
    } catch (c) {
    }
    OpenLayers.Util.alphaHackNeeded = (b && (a >= 5.5) && (a < 7))
  }
  return OpenLayers.Util.alphaHackNeeded
};
OpenLayers.Util.modifyAlphaImageDiv = function (a, b, j, i, g, f, c, d, h) {
  OpenLayers.Util.modifyDOMElement(a, b, j, i, f, null, null, h);
  var e = a.childNodes[0];
  if (g) {
    e.src = g
  }
  OpenLayers.Util.modifyDOMElement(e, a.id + '_innerImage', null, i, 'relative', c);
  if (OpenLayers.Util.alphaHack()) {
    if (a.style.display != 'none') {
      a.style.display = 'inline-block'
    }
    if (d == null) {
      d = 'scale'
    }
    a.style.filter = 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'' + e.src + '\', sizingMethod=\'' + d + '\')';
    if (parseFloat(a.style.opacity) >= 0 && parseFloat(a.style.opacity) < 1) {
      a.style.filter += ' alpha(opacity=' + a.style.opacity * 100 + ')'
    }
    e.style.filter = 'alpha(opacity=0)'
  }
};
OpenLayers.Util.createAlphaImageDiv = function (b, j, i, g, f, c, d, h, k) {
  var a = OpenLayers.Util.createDiv();
  var e = OpenLayers.Util.createImage(null, null, null, null, null, null, null, k);
  e.className = 'olAlphaImg';
  a.appendChild(e);
  OpenLayers.Util.modifyAlphaImageDiv(a, b, j, i, g, f, c, d, h);
  return a
};
OpenLayers.Util.upperCaseObject = function (b) {
  var a = {
  };
  for (var c in b) {
    a[c.toUpperCase()] = b[c]
  }
  return a
};
OpenLayers.Util.applyDefaults = function (d, c) {
  d = d || {
  };
  var b = typeof window.Event == 'function' && c instanceof window.Event;
  for (var a in c) {
    if (d[a] === undefined || (!b && c.hasOwnProperty && c.hasOwnProperty(a) && !d.hasOwnProperty(a))) {
      d[a] = c[a]
    }
  }
  if (!b && c && c.hasOwnProperty && c.hasOwnProperty('toString') && !d.hasOwnProperty('toString')) {
    d.toString = c.toString
  }
  return d
};
OpenLayers.Util.getParameterString = function (c) {
  var b = [
  ];
  for (var h in c) {
    var g = c[h];
    if ((g != null) && (typeof g != 'function')) {
      var d;
      if (typeof g == 'object' && g.constructor == Array) {
        var e = [
        ];
        var i;
        for (var a = 0, f = g.length; a < f; a++) {
          i = g[a];
          e.push(encodeURIComponent((i === null || i === undefined) ? '' : i))
        }
        d = e.join(',')
      } else {
        d = encodeURIComponent(g)
      }
      b.push(encodeURIComponent(h) + '=' + d)
    }
  }
  return b.join('&')
};
OpenLayers.Util.urlAppend = function (a, b) {
  var d = a;
  if (b) {
    var c = (a + ' ').split(/[?&]/);
    d += (c.pop() === ' ' ? b : c.length ? '&' + b : '?' + b)
  }
  return d
};
OpenLayers.Util.getImagesLocation = function () {
  return OpenLayers.ImgPath || (OpenLayers._getScriptLocation() + 'img/')
};
OpenLayers.Util.getImageLocation = function (a) {
  return OpenLayers.Util.getImagesLocation() + a
};
OpenLayers.Util.Try = function () {
  var d = null;
  for (var c = 0, a = arguments.length; c < a; c++) {
    var b = arguments[c];
    try {
      d = b();
      break
    } catch (f) {
    }
  }
  return d
};
OpenLayers.Util.getXmlNodeValue = function (a) {
  var b = null;
  OpenLayers.Util.Try(function () {
    b = a.text;
    if (!b) {
      b = a.textContent
    }
    if (!b) {
      b = a.firstChild.nodeValue
    }
  }, function () {
    b = a.textContent
  });
  return b
};
OpenLayers.Util.mouseLeft = function (a, c) {
  var b = (a.relatedTarget) ? a.relatedTarget : a.toElement;
  while (b != c && b != null) {
    b = b.parentNode
  }
  return (b != c)
};
OpenLayers.Util.DEFAULT_PRECISION = 14;
OpenLayers.Util.toFloat = function (b, a) {
  if (a == null) {
    a = OpenLayers.Util.DEFAULT_PRECISION
  }
  if (typeof b !== 'number') {
    b = parseFloat(b)
  }
  return a === 0 ? b : parseFloat(b.toPrecision(a))
};
OpenLayers.Util.rad = function (a) {
  return a * Math.PI / 180
};
OpenLayers.Util.deg = function (a) {
  return a * 180 / Math.PI
};
OpenLayers.Util.VincentyConstants = {
  a: 6378137,
  b: 6356752.3142,
  f: 1 / 298.257223563
};
OpenLayers.Util.distVincenty = function (g, e) {
  var k = OpenLayers.Util.VincentyConstants;
  var M = k.a,
  K = k.b,
  G = k.f;
  var n = OpenLayers.Util.rad(e.lon - g.lon);
  var J = Math.atan((1 - G) * Math.tan(OpenLayers.Util.rad(g.lat)));
  var I = Math.atan((1 - G) * Math.tan(OpenLayers.Util.rad(e.lat)));
  var m = Math.sin(J),
  i = Math.cos(J);
  var l = Math.sin(I),
  h = Math.cos(I);
  var r = n,
  o = 2 * Math.PI;
  var q = 20;
  while (Math.abs(r - o) > 1e-12 && --q > 0) {
    var z = Math.sin(r),
    c = Math.cos(r);
    var N = Math.sqrt((h * z) * (h * z) + (i * l - m * h * c) * (i * l - m * h * c));
    if (N == 0) {
      return 0
    }
    var E = m * l + i * h * c;
    var y = Math.atan2(N, E);
    var j = Math.asin(i * h * z / N);
    var F = Math.cos(j) * Math.cos(j);
    var p = E - 2 * m * l / F;
    var v = G / 16 * F * (4 + G * (4 - 3 * F));
    o = r;
    r = n + (1 - v) * G * Math.sin(j) * (y + v * N * (p + v * E * ( - 1 + 2 * p * p)))
  }
  if (q == 0) {
    return NaN
  }
  var u = F * (M * M - K * K) / (K * K);
  var x = 1 + u / 16384 * (4096 + u * ( - 768 + u * (320 - 175 * u)));
  var w = u / 1024 * (256 + u * ( - 128 + u * (74 - 47 * u)));
  var D = w * N * (p + w / 4 * (E * ( - 1 + 2 * p * p) - w / 6 * p * ( - 3 + 4 * N * N) * ( - 3 + 4 * p * p)));
  var t = K * x * (y - D);
  var H = t.toFixed(3) / 1000;
  return H
};
OpenLayers.Util.destinationVincenty = function (l, P, E) {
  var o = OpenLayers.Util;
  var i = o.VincentyConstants;
  var Q = i.a,
  O = i.b,
  J = i.f;
  var N = l.lon;
  var g = l.lat;
  var q = E;
  var D = o.rad(P);
  var G = Math.sin(D);
  var h = Math.cos(D);
  var F = (1 - J) * Math.tan(o.rad(g));
  var c = 1 / Math.sqrt((1 + F * F)),
  j = F * c;
  var p = Math.atan2(F, h);
  var y = c * G;
  var I = 1 - y * y;
  var t = I * (Q * Q - O * O) / (O * O);
  var x = 1 + t / 16384 * (4096 + t * ( - 768 + t * (320 - 175 * t)));
  var v = t / 1024 * (256 + t * ( - 128 + t * (74 - 47 * t)));
  var w = q / (O * x),
  K = 2 * Math.PI;
  while (Math.abs(w - K) > 1e-12) {
    var m = Math.cos(2 * p + w);
    var R = Math.sin(w);
    var H = Math.cos(w);
    var z = v * R * (m + v / 4 * (H * ( - 1 + 2 * m * m) - v / 6 * m * ( - 3 + 4 * R * R) * ( - 3 + 4 * m * m)));
    K = w;
    w = q / (O * x) + z
  }
  var M = j * R - c * H * h;
  var d = Math.atan2(j * H + c * R * h, (1 - J) * Math.sqrt(y * y + M * M));
  var n = Math.atan2(R * G, c * H - j * R * h);
  var r = J / 16 * I * (4 + J * (4 - 3 * I));
  var k = n - (1 - r) * J * y * (w + r * R * (m + r * H * ( - 1 + 2 * m * m)));
  var e = Math.atan2(y, - M);
  return new OpenLayers.LonLat(N + o.deg(k), o.deg(d))
};
OpenLayers.Util.getParameters = function (b, n) {
  n = n || {
  };
  b = (b === null || b === undefined) ? window.location.href : b;
  var a = '';
  if (OpenLayers.String.contains(b, '?')) {
    var c = b.indexOf('?') + 1;
    var f = OpenLayers.String.contains(b, '#') ? b.indexOf('#')  : b.length;
    a = b.substring(c, f)
  }
  var m = {
  };
  var d = a.split(/[&;]/);
  for (var h = 0, j = d.length; h < j; ++h) {
    var g = d[h].split('=');
    if (g[0]) {
      var l = g[0];
      try {
        l = decodeURIComponent(l)
      } catch (e) {
        l = unescape(l)
      }
      var k = (g[1] || '').replace(/\+/g, ' ');
      try {
        k = decodeURIComponent(k)
      } catch (e) {
        k = unescape(k)
      }
      if (n.splitArgs !== false) {
        k = k.split(',')
      }
      if (k.length == 1) {
        k = k[0]
      }
      m[l] = k
    }
  }
  return m
};
OpenLayers.Util.lastSeqID = 0;
OpenLayers.Util.createUniqueID = function (a) {
  if (a == null) {
    a = 'id_'
  } else {
    a = a.replace(OpenLayers.Util.dotless, '_')
  }
  OpenLayers.Util.lastSeqID += 1;
  return a + OpenLayers.Util.lastSeqID
};
OpenLayers.INCHES_PER_UNIT = {
  inches: 1,
  ft: 12,
  mi: 63360,
  m: 39.37,
  km: 39370,
  dd: 4374754,
  yd: 36
};
OpenLayers.INCHES_PER_UNIT['in'] = OpenLayers.INCHES_PER_UNIT.inches;
OpenLayers.INCHES_PER_UNIT.degrees = OpenLayers.INCHES_PER_UNIT.dd;
OpenLayers.INCHES_PER_UNIT.nmi = 1852 * OpenLayers.INCHES_PER_UNIT.m;
OpenLayers.METERS_PER_INCH = 0.0254000508001016;
OpenLayers.Util.extend(OpenLayers.INCHES_PER_UNIT, {
  Inch: OpenLayers.INCHES_PER_UNIT.inches,
  Meter: 1 / OpenLayers.METERS_PER_INCH,
  Foot: 0.3048006096012192 / OpenLayers.METERS_PER_INCH,
  IFoot: 0.3048 / OpenLayers.METERS_PER_INCH,
  ClarkeFoot: 0.3047972651151 / OpenLayers.METERS_PER_INCH,
  SearsFoot: 0.30479947153867626 / OpenLayers.METERS_PER_INCH,
  GoldCoastFoot: 0.3047997101815088 / OpenLayers.METERS_PER_INCH,
  IInch: 0.0254 / OpenLayers.METERS_PER_INCH,
  MicroInch: 0.0000254 / OpenLayers.METERS_PER_INCH,
  Mil: 2.54e-8 / OpenLayers.METERS_PER_INCH,
  Centimeter: 0.01 / OpenLayers.METERS_PER_INCH,
  Kilometer: 1000 / OpenLayers.METERS_PER_INCH,
  Yard: 0.9144018288036576 / OpenLayers.METERS_PER_INCH,
  SearsYard: 0.914398414616029 / OpenLayers.METERS_PER_INCH,
  IndianYard: 0.9143985307444408 / OpenLayers.METERS_PER_INCH,
  IndianYd37: 0.91439523 / OpenLayers.METERS_PER_INCH,
  IndianYd62: 0.9143988 / OpenLayers.METERS_PER_INCH,
  IndianYd75: 0.9143985 / OpenLayers.METERS_PER_INCH,
  IndianFoot: 0.30479951 / OpenLayers.METERS_PER_INCH,
  IndianFt37: 0.30479841 / OpenLayers.METERS_PER_INCH,
  IndianFt62: 0.3047996 / OpenLayers.METERS_PER_INCH,
  IndianFt75: 0.3047995 / OpenLayers.METERS_PER_INCH,
  Mile: 1609.3472186944373 / OpenLayers.METERS_PER_INCH,
  IYard: 0.9144 / OpenLayers.METERS_PER_INCH,
  IMile: 1609.344 / OpenLayers.METERS_PER_INCH,
  NautM: 1852 / OpenLayers.METERS_PER_INCH,
  'Lat-66': 110943.31648893273 / OpenLayers.METERS_PER_INCH,
  'Lat-83': 110946.25736872235 / OpenLayers.METERS_PER_INCH,
  Decimeter: 0.1 / OpenLayers.METERS_PER_INCH,
  Millimeter: 0.001 / OpenLayers.METERS_PER_INCH,
  Dekameter: 10 / OpenLayers.METERS_PER_INCH,
  Decameter: 10 / OpenLayers.METERS_PER_INCH,
  Hectometer: 100 / OpenLayers.METERS_PER_INCH,
  GermanMeter: 1.0000135965 / OpenLayers.METERS_PER_INCH,
  CaGrid: 0.999738 / OpenLayers.METERS_PER_INCH,
  ClarkeChain: 20.1166194976 / OpenLayers.METERS_PER_INCH,
  GunterChain: 20.11684023368047 / OpenLayers.METERS_PER_INCH,
  BenoitChain: 20.116782494375872 / OpenLayers.METERS_PER_INCH,
  SearsChain: 20.11676512155 / OpenLayers.METERS_PER_INCH,
  ClarkeLink: 0.201166194976 / OpenLayers.METERS_PER_INCH,
  GunterLink: 0.2011684023368047 / OpenLayers.METERS_PER_INCH,
  BenoitLink: 0.20116782494375873 / OpenLayers.METERS_PER_INCH,
  SearsLink: 0.2011676512155 / OpenLayers.METERS_PER_INCH,
  Rod: 5.02921005842012 / OpenLayers.METERS_PER_INCH,
  IntnlChain: 20.1168 / OpenLayers.METERS_PER_INCH,
  IntnlLink: 0.201168 / OpenLayers.METERS_PER_INCH,
  Perch: 5.02921005842012 / OpenLayers.METERS_PER_INCH,
  Pole: 5.02921005842012 / OpenLayers.METERS_PER_INCH,
  Furlong: 201.1684023368046 / OpenLayers.METERS_PER_INCH,
  Rood: 3.778266898 / OpenLayers.METERS_PER_INCH,
  CapeFoot: 0.3047972615 / OpenLayers.METERS_PER_INCH,
  Brealey: 375 / OpenLayers.METERS_PER_INCH,
  ModAmFt: 0.304812252984506 / OpenLayers.METERS_PER_INCH,
  Fathom: 1.8288 / OpenLayers.METERS_PER_INCH,
  'NautM-UK': 1853.184 / OpenLayers.METERS_PER_INCH,
  '50kilometers': 50000 / OpenLayers.METERS_PER_INCH,
  '150kilometers': 150000 / OpenLayers.METERS_PER_INCH
});
OpenLayers.Util.extend(OpenLayers.INCHES_PER_UNIT, {
  mm: OpenLayers.INCHES_PER_UNIT.Meter / 1000,
  cm: OpenLayers.INCHES_PER_UNIT.Meter / 100,
  dm: OpenLayers.INCHES_PER_UNIT.Meter * 100,
  km: OpenLayers.INCHES_PER_UNIT.Meter * 1000,
  kmi: OpenLayers.INCHES_PER_UNIT.nmi,
  fath: OpenLayers.INCHES_PER_UNIT.Fathom,
  ch: OpenLayers.INCHES_PER_UNIT.IntnlChain,
  link: OpenLayers.INCHES_PER_UNIT.IntnlLink,
  'us-in': OpenLayers.INCHES_PER_UNIT.inches,
  'us-ft': OpenLayers.INCHES_PER_UNIT.Foot,
  'us-yd': OpenLayers.INCHES_PER_UNIT.Yard,
  'us-ch': OpenLayers.INCHES_PER_UNIT.GunterChain,
  'us-mi': OpenLayers.INCHES_PER_UNIT.Mile,
  'ind-yd': OpenLayers.INCHES_PER_UNIT.IndianYd37,
  'ind-ft': OpenLayers.INCHES_PER_UNIT.IndianFt37,
  'ind-ch': 20.11669506 / OpenLayers.METERS_PER_INCH
});
OpenLayers.DOTS_PER_INCH = 72;
OpenLayers.Util.normalizeScale = function (b) {
  var a = (b > 1) ? (1 / b)  : b;
  return a
};
OpenLayers.Util.getResolutionFromScale = function (d, a) {
  var b;
  if (d) {
    if (a == null) {
      a = 'degrees'
    }
    var c = OpenLayers.Util.normalizeScale(d);
    b = 1 / (c * OpenLayers.INCHES_PER_UNIT[a] * OpenLayers.DOTS_PER_INCH)
  }
  return b
};
OpenLayers.Util.getScaleFromResolution = function (b, a) {
  if (a == null) {
    a = 'degrees'
  }
  var c = b * OpenLayers.INCHES_PER_UNIT[a] * OpenLayers.DOTS_PER_INCH;
  return c
};
OpenLayers.Util.pagePosition = function (d) {
  var i = [
    0,
    0
  ];
  var h = OpenLayers.Util.getViewportElement();
  if (!d || d == window || d == h) {
    return i
  }
  var f = OpenLayers.IS_GECKO && document.getBoxObjectFor && OpenLayers.Element.getStyle(d, 'position') == 'absolute' && (d.style.top == '' || d.style.left == '');
  var j = null;
  var g;
  if (d.getBoundingClientRect) {
    g = d.getBoundingClientRect();
    var b = window.pageYOffset || h.scrollTop;
    var c = window.pageXOffset || h.scrollLeft;
    i[0] = g.left + c;
    i[1] = g.top + b
  } else {
    if (document.getBoxObjectFor && !f) {
      g = document.getBoxObjectFor(d);
      var a = document.getBoxObjectFor(h);
      i[0] = g.screenX - a.screenX;
      i[1] = g.screenY - a.screenY
    } else {
      i[0] = d.offsetLeft;
      i[1] = d.offsetTop;
      j = d.offsetParent;
      if (j != d) {
        while (j) {
          i[0] += j.offsetLeft;
          i[1] += j.offsetTop;
          j = j.offsetParent
        }
      }
      var e = OpenLayers.BROWSER_NAME;
      if (e == 'opera' || (e == 'safari' && OpenLayers.Element.getStyle(d, 'position') == 'absolute')) {
        i[1] -= document.body.offsetTop
      }
      j = d.offsetParent;
      while (j && j != document.body) {
        i[0] -= j.scrollLeft;
        if (e != 'opera' || j.tagName != 'TR') {
          i[1] -= j.scrollTop
        }
        j = j.offsetParent
      }
    }
  }
  return i
};
OpenLayers.Util.getViewportElement = function () {
  var a = arguments.callee.viewportElement;
  if (a == undefined) {
    a = (OpenLayers.BROWSER_NAME == 'msie' && document.compatMode != 'CSS1Compat') ? document.body : document.documentElement;
    arguments.callee.viewportElement = a
  }
  return a
};
OpenLayers.Util.isEquivalentUrl = function (f, e, c) {
  c = c || {
  };
  OpenLayers.Util.applyDefaults(c, {
    ignoreCase: true,
    ignorePort80: true,
    ignoreHash: true,
    splitArgs: false
  });
  var b = OpenLayers.Util.createUrlObject(f, c);
  var a = OpenLayers.Util.createUrlObject(e, c);
  for (var d in b) {
    if (d !== 'args') {
      if (b[d] != a[d]) {
        return false
      }
    }
  }
  for (var d in b.args) {
    if (b.args[d] != a.args[d]) {
      return false
    }
    delete a.args[d]
  }
  for (var d in a.args) {
    return false
  }
  return true
};
OpenLayers.Util.createUrlObject = function (c, k) {
  k = k || {
  };
  if (!(/^\w+:\/\//).test(c)) {
    var g = window.location;
    var e = g.port ? ':' + g.port : '';
    var h = g.protocol + '//' + g.host.split(':').shift() + e;
    if (c.indexOf('/') === 0) {
      c = h + c
    } else {
      var f = g.pathname.split('/');
      f.pop();
      c = h + f.join('/') + '/' + c
    }
  }
  if (k.ignoreCase) {
    c = c.toLowerCase()
  }
  var i = document.createElement('a');
  i.href = c;
  var d = {
  };
  d.host = i.host.split(':').shift();
  d.protocol = i.protocol;
  if (k.ignorePort80) {
    d.port = (i.port == '80' || i.port == '0') ? '' : i.port
  } else {
    d.port = (i.port == '' || i.port == '0') ? '80' : i.port
  }
  d.hash = (k.ignoreHash || i.hash === '#') ? '' : i.hash;
  var b = i.search;
  if (!b) {
    var j = c.indexOf('?');
    b = (j != - 1) ? c.substr(j)  : ''
  }
  d.args = OpenLayers.Util.getParameters(b, {
    splitArgs: k.splitArgs
  });
  d.pathname = (i.pathname.charAt(0) == '/') ? i.pathname : '/' + i.pathname;
  return d
};
OpenLayers.Util.removeTail = function (b) {
  var c = null;
  var a = b.indexOf('?');
  var d = b.indexOf('#');
  if (a == - 1) {
    c = (d != - 1) ? b.substr(0, d)  : b
  } else {
    c = (d != - 1) ? b.substr(0, Math.min(a, d))  : b.substr(0, a)
  }
  return c
};
OpenLayers.IS_GECKO = (function () {
  var a = navigator.userAgent.toLowerCase();
  return a.indexOf('webkit') == - 1 && a.indexOf('gecko') != - 1
}) ();
OpenLayers.CANVAS_SUPPORTED = (function () {
  var a = document.createElement('canvas');
  return !!(a.getContext && a.getContext('2d'))
}) ();
OpenLayers.BROWSER_NAME = (function () {
  var a = '';
  var b = navigator.userAgent.toLowerCase();
  if (b.indexOf('opera') != - 1) {
    a = 'opera'
  } else {
    if (b.indexOf('msie') != - 1) {
      a = 'msie'
    } else {
      if (b.indexOf('safari') != - 1) {
        a = 'safari'
      } else {
        if (b.indexOf('mozilla') != - 1) {
          if (b.indexOf('firefox') != - 1) {
            a = 'firefox'
          } else {
            a = 'mozilla'
          }
        }
      }
    }
  }
  return a
}) ();
OpenLayers.Util.getBrowserName = function () {
  return OpenLayers.BROWSER_NAME
};
OpenLayers.Util.getRenderedDimensions = function (b, o, p) {
  var m,
  e;
  var a = document.createElement('div');
  a.style.visibility = 'hidden';
  var n = (p && p.containerElement) ? p.containerElement : document.body;
  var q = false;
  var g = null;
  var k = n;
  while (k && k.tagName.toLowerCase() != 'body') {
    var j = OpenLayers.Element.getStyle(k, 'position');
    if (j == 'absolute') {
      q = true;
      break
    } else {
      if (j && j != 'static') {
        break
      }
    }
    k = k.parentNode
  }
  if (q && (n.clientHeight === 0 || n.clientWidth === 0)) {
    g = document.createElement('div');
    g.style.visibility = 'hidden';
    g.style.position = 'absolute';
    g.style.overflow = 'visible';
    g.style.width = document.body.clientWidth + 'px';
    g.style.height = document.body.clientHeight + 'px';
    g.appendChild(a)
  }
  a.style.position = 'absolute';
  if (o) {
    if (o.w) {
      m = o.w;
      a.style.width = m + 'px'
    } else {
      if (o.h) {
        e = o.h;
        a.style.height = e + 'px'
      }
    }
  }
  if (p && p.displayClass) {
    a.className = p.displayClass
  }
  var f = document.createElement('div');
  f.innerHTML = b;
  f.style.overflow = 'visible';
  if (f.childNodes) {
    for (var d = 0, c = f.childNodes.length; d < c; d++) {
      if (!f.childNodes[d].style) {
        continue
      }
      f.childNodes[d].style.overflow = 'visible'
    }
  }
  a.appendChild(f);
  if (g) {
    n.appendChild(g)
  } else {
    n.appendChild(a)
  }
  if (!m) {
    m = parseInt(f.scrollWidth);
    a.style.width = m + 'px'
  }
  if (!e) {
    e = parseInt(f.scrollHeight)
  }
  a.removeChild(f);
  if (g) {
    g.removeChild(a);
    n.removeChild(g)
  } else {
    n.removeChild(a)
  }
  return new OpenLayers.Size(m, e)
};
OpenLayers.Util.getScrollbarWidth = function () {
  var c = OpenLayers.Util._scrollbarWidth;
  if (c == null) {
    var e = null;
    var d = null;
    var a = 0;
    var b = 0;
    e = document.createElement('div');
    e.style.position = 'absolute';
    e.style.top = '-1000px';
    e.style.left = '-1000px';
    e.style.width = '100px';
    e.style.height = '50px';
    e.style.overflow = 'hidden';
    d = document.createElement('div');
    d.style.width = '100%';
    d.style.height = '200px';
    e.appendChild(d);
    document.body.appendChild(e);
    a = d.offsetWidth;
    e.style.overflow = 'scroll';
    b = d.offsetWidth;
    document.body.removeChild(document.body.lastChild);
    OpenLayers.Util._scrollbarWidth = (a - b);
    c = OpenLayers.Util._scrollbarWidth
  }
  return c
};
OpenLayers.Util.getFormattedLonLat = function (h, b, e) {
  if (!e) {
    e = 'dms'
  }
  h = (h + 540) % 360 - 180;
  var d = Math.abs(h);
  var i = Math.floor(d);
  var a = (d - i) / (1 / 60);
  var c = a;
  a = Math.floor(a);
  var g = (c - a) / (1 / 60);
  g = Math.round(g * 10);
  g /= 10;
  if (g >= 60) {
    g -= 60;
    a += 1;
    if (a >= 60) {
      a -= 60;
      i += 1
    }
  }
  if (i < 10) {
    i = '0' + i
  }
  var f = i + '°';
  if (e.indexOf('dm') >= 0) {
    if (a < 10) {
      a = '0' + a
    }
    f += a + '\'';
    if (e.indexOf('dms') >= 0) {
      if (g < 10) {
        g = '0' + g
      }
      f += g + '"'
    }
  }
  if (b == 'lon') {
    f += h < 0 ? OpenLayers.i18n('W')  : OpenLayers.i18n('E')
  } else {
    f += h < 0 ? OpenLayers.i18n('S')  : OpenLayers.i18n('N')
  }
  return f
};
OpenLayers.Util = OpenLayers.Util || {
};
OpenLayers.Util.vendorPrefix = (function () {
  var d = [
    '',
    'O',
    'ms',
    'Moz',
    'Webkit'
  ],
  a = document.createElement('div').style,
  e = {
  },
  g = {
  };
  function h(i) {
    if (!i) {
      return null
    }
    return i.replace(/([A-Z])/g, function (j) {
      return '-' + j.toLowerCase()
    }).replace(/^ms-/, '-ms-')
  }
  function b(k) {
    if (e[k] === undefined) {
      var j = k.replace(/(-[\s\S])/g, function (l) {
        return l.charAt(1).toUpperCase()
      });
      var i = c(j);
      e[k] = h(i)
    }
    return e[k]
  }
  function f(p, o) {
    if (g[o] === undefined) {
      var q,
      k = 0,
      j = d.length,
      n,
      m = (typeof p.cssText !== 'undefined');
      g[o] = null;
      for (; k < j; k++) {
        n = d[k];
        if (n) {
          if (!m) {
            n = n.toLowerCase()
          }
          q = n + o.charAt(0).toUpperCase() + o.slice(1)
        } else {
          q = o
        }
        if (p[q] !== undefined) {
          g[o] = q;
          break
        }
      }
    }
    return g[o]
  }
  function c(i) {
    return f(a, i)
  }
  return {
    css: b,
    js: f,
    style: c,
    cssCache: e,
    jsCache: g
  }
}());
OpenLayers.Bounds = OpenLayers.Class({
  left: null,
  bottom: null,
  right: null,
  top: null,
  centerLonLat: null,
  initialize: function (d, a, b, c) {
    if (OpenLayers.Util.isArray(d)) {
      c = d[3];
      b = d[2];
      a = d[1];
      d = d[0]
    }
    if (d != null) {
      this.left = OpenLayers.Util.toFloat(d)
    }
    if (a != null) {
      this.bottom = OpenLayers.Util.toFloat(a)
    }
    if (b != null) {
      this.right = OpenLayers.Util.toFloat(b)
    }
    if (c != null) {
      this.top = OpenLayers.Util.toFloat(c)
    }
  },
  clone: function () {
    return new OpenLayers.Bounds(this.left, this.bottom, this.right, this.top)
  },
  equals: function (b) {
    var a = false;
    if (b != null) {
      a = ((this.left == b.left) && (this.right == b.right) && (this.top == b.top) && (this.bottom == b.bottom))
    }
    return a
  },
  toString: function () {
    return [this.left,
    this.bottom,
    this.right,
    this.top].join(',')
  },
  toArray: function (a) {
    if (a === true) {
      return [this.bottom,
      this.left,
      this.top,
      this.right]
    } else {
      return [this.left,
      this.bottom,
      this.right,
      this.top]
    }
  },
  toBBOX: function (b, e) {
    if (b == null) {
      b = 6
    }
    var g = Math.pow(10, b);
    var f = Math.round(this.left * g) / g;
    var d = Math.round(this.bottom * g) / g;
    var c = Math.round(this.right * g) / g;
    var a = Math.round(this.top * g) / g;
    if (e === true) {
      return d + ',' + f + ',' + a + ',' + c
    } else {
      return f + ',' + d + ',' + c + ',' + a
    }
  },
  toGeometry: function () {
    return new OpenLayers.Geometry.Polygon([new OpenLayers.Geometry.LinearRing([new OpenLayers.Geometry.Point(this.left, this.bottom),
    new OpenLayers.Geometry.Point(this.right, this.bottom),
    new OpenLayers.Geometry.Point(this.right, this.top),
    new OpenLayers.Geometry.Point(this.left, this.top)])])
  },
  getWidth: function () {
    return (this.right - this.left)
  },
  getHeight: function () {
    return (this.top - this.bottom)
  },
  getSize: function () {
    return new OpenLayers.Size(this.getWidth(), this.getHeight())
  },
  getCenterPixel: function () {
    return new OpenLayers.Pixel((this.left + this.right) / 2, (this.bottom + this.top) / 2)
  },
  getCenterLonLat: function () {
    if (!this.centerLonLat) {
      this.centerLonLat = new OpenLayers.LonLat((this.left + this.right) / 2, (this.bottom + this.top) / 2)
    }
    return this.centerLonLat
  },
  scale: function (e, c) {
    if (c == null) {
      c = this.getCenterLonLat()
    }
    var a,
    h;
    if (c.CLASS_NAME == 'OpenLayers.LonLat') {
      a = c.lon;
      h = c.lat
    } else {
      a = c.x;
      h = c.y
    }
    var g = (this.left - a) * e + a;
    var b = (this.bottom - h) * e + h;
    var d = (this.right - a) * e + a;
    var f = (this.top - h) * e + h;
    return new OpenLayers.Bounds(g, b, d, f)
  },
  add: function (a, b) {
    if ((a == null) || (b == null)) {
      throw new TypeError('Bounds.add cannot receive null values')
    }
    return new OpenLayers.Bounds(this.left + a, this.bottom + b, this.right + a, this.top + b)
  },
  extend: function (a) {
    if (a) {
      switch (a.CLASS_NAME) {
        case 'OpenLayers.LonLat':
          this.extendXY(a.lon, a.lat);
          break;
        case 'OpenLayers.Geometry.Point':
          this.extendXY(a.x, a.y);
          break;
        case 'OpenLayers.Bounds':
          this.centerLonLat = null;
          if ((this.left == null) || (a.left < this.left)) {
            this.left = a.left
          }
          if ((this.bottom == null) || (a.bottom < this.bottom)) {
            this.bottom = a.bottom
          }
          if ((this.right == null) || (a.right > this.right)) {
            this.right = a.right
          }
          if ((this.top == null) || (a.top > this.top)) {
            this.top = a.top
          }
          break
      }
    }
  },
  extendXY: function (a, b) {
    this.centerLonLat = null;
    if ((this.left == null) || (a < this.left)) {
      this.left = a
    }
    if ((this.bottom == null) || (b < this.bottom)) {
      this.bottom = b
    }
    if ((this.right == null) || (a > this.right)) {
      this.right = a
    }
    if ((this.top == null) || (b > this.top)) {
      this.top = b
    }
  },
  containsLonLat: function (f, c) {
    if (typeof c === 'boolean') {
      c = {
        inclusive: c
      }
    }
    c = c || {
    };
    var d = this.contains(f.lon, f.lat, c.inclusive),
    e = c.worldBounds;
    if (e && !d) {
      var g = e.getWidth();
      var a = (e.left + e.right) / 2;
      var b = Math.round((f.lon - a) / g);
      d = this.containsLonLat({
        lon: f.lon - b * g,
        lat: f.lat
      }, {
        inclusive: c.inclusive
      })
    }
    return d
  },
  containsPixel: function (b, a) {
    return this.contains(b.x, b.y, a)
  },
  contains: function (b, d, a) {
    if (a == null) {
      a = true
    }
    if (b == null || d == null) {
      return false
    }
    b = OpenLayers.Util.toFloat(b);
    d = OpenLayers.Util.toFloat(d);
    var c = false;
    if (a) {
      c = ((b >= this.left) && (b <= this.right) && (d >= this.bottom) && (d <= this.top))
    } else {
      c = ((b > this.left) && (b < this.right) && (d > this.bottom) && (d < this.top))
    }
    return c
  },
  intersectsBounds: function (a, m) {
    if (typeof m === 'boolean') {
      m = {
        inclusive: m
      }
    }
    m = m || {
    };
    if (m.worldBounds) {
      var l = this.wrapDateLine(m.worldBounds);
      a = a.wrapDateLine(m.worldBounds)
    } else {
      l = this
    }
    if (m.inclusive == null) {
      m.inclusive = true
    }
    var h = false;
    var i = (l.left == a.right || l.right == a.left || l.top == a.bottom || l.bottom == a.top);
    if (m.inclusive || !i) {
      var j = (((a.bottom >= l.bottom) && (a.bottom <= l.top)) || ((l.bottom >= a.bottom) && (l.bottom <= a.top)));
      var k = (((a.top >= l.bottom) && (a.top <= l.top)) || ((l.top > a.bottom) && (l.top < a.top)));
      var d = (((a.left >= l.left) && (a.left <= l.right)) || ((l.left >= a.left) && (l.left <= a.right)));
      var c = (((a.right >= l.left) && (a.right <= l.right)) || ((l.right >= a.left) && (l.right <= a.right)));
      h = ((j || k) && (d || c))
    }
    if (m.worldBounds && !h) {
      var g = m.worldBounds;
      var b = g.getWidth();
      var f = !g.containsBounds(l);
      var e = !g.containsBounds(a);
      if (f && !e) {
        a = a.add( - b, 0);
        h = l.intersectsBounds(a, {
          inclusive: m.inclusive
        })
      } else {
        if (e && !f) {
          l = l.add( - b, 0);
          h = a.intersectsBounds(l, {
            inclusive: m.inclusive
          })
        }
      }
    }
    return h
  },
  containsBounds: function (g, b, a) {
    if (b == null) {
      b = false
    }
    if (a == null) {
      a = true
    }
    var c = this.contains(g.left, g.bottom, a);
    var d = this.contains(g.right, g.bottom, a);
    var f = this.contains(g.left, g.top, a);
    var e = this.contains(g.right, g.top, a);
    return (b) ? (c || d || f || e)  : (c && d && f && e)
  },
  determineQuadrant: function (c) {
    var b = '';
    var a = this.getCenterLonLat();
    b += (c.lat < a.lat) ? 'b' : 't';
    b += (c.lon < a.lon) ? 'l' : 'r';
    return b
  },
  transform: function (d, b) {
    this.centerLonLat = null;
    var e = OpenLayers.Projection.transform({
      x: this.left,
      y: this.bottom
    }, d, b);
    var a = OpenLayers.Projection.transform({
      x: this.right,
      y: this.bottom
    }, d, b);
    var c = OpenLayers.Projection.transform({
      x: this.left,
      y: this.top
    }, d, b);
    var f = OpenLayers.Projection.transform({
      x: this.right,
      y: this.top
    }, d, b);
    this.left = Math.min(e.x, c.x);
    this.bottom = Math.min(e.y, a.y);
    this.right = Math.max(a.x, f.x);
    this.top = Math.max(c.y, f.y);
    return this
  },
  wrapDateLine: function (a, c) {
    c = c || {
    };
    var e = c.leftTolerance || 0;
    var b = c.rightTolerance || 0;
    var g = this.clone();
    if (a) {
      var d = a.getWidth();
      while (g.left < a.left && g.right - b <= a.left) {
        g = g.add(d, 0)
      }
      while (g.left + e >= a.right && g.right > a.right) {
        g = g.add( - d, 0)
      }
      var f = g.left + e;
      if (f < a.right && f > a.left && g.right - b > a.right) {
        g = g.add( - d, 0)
      }
    }
    return g
  },
  CLASS_NAME: 'OpenLayers.Bounds'
}); OpenLayers.Bounds.fromString = function (c, b) {
  var a = c.split(',');
  return OpenLayers.Bounds.fromArray(a, b)
}; OpenLayers.Bounds.fromArray = function (b, a) {
  return a === true ? new OpenLayers.Bounds(b[1], b[0], b[3], b[2])  : new OpenLayers.Bounds(b[0], b[1], b[2], b[3])
}; OpenLayers.Bounds.fromSize = function (a) {
  return new OpenLayers.Bounds(0, a.h, a.w, 0)
}; OpenLayers.Bounds.oppositeQuadrant = function (a) {
  var b = '';
  b += (a.charAt(0) == 't') ? 'b' : 't';
  b += (a.charAt(1) == 'l') ? 'r' : 'l';
  return b
}; OpenLayers.Element = {
  visible: function (a) {
    return OpenLayers.Util.getElement(a).style.display != 'none'
  },
  toggle: function () {
    for (var c = 0, a = arguments.length; c < a; c++) {
      var b = OpenLayers.Util.getElement(arguments[c]);
      var d = OpenLayers.Element.visible(b) ? 'none' : '';
      b.style.display = d
    }
  },
  remove: function (a) {
    a = OpenLayers.Util.getElement(a);
    a.parentNode.removeChild(a)
  },
  getHeight: function (a) {
    a = OpenLayers.Util.getElement(a);
    return a.offsetHeight
  },
  hasClass: function (b, a) {
    var c = b.className;
    return (!!c && new RegExp('(^|\\s)' + a + '(\\s|$)').test(c))
  },
  addClass: function (b, a) {
    if (!OpenLayers.Element.hasClass(b, a)) {
      b.className += (b.className ? ' ' : '') + a
    }
    return b
  },
  removeClass: function (b, a) {
    var c = b.className;
    if (c) {
      b.className = OpenLayers.String.trim(c.replace(new RegExp('(^|\\s+)' + a + '(\\s+|$)'), ' '))
    }
    return b
  },
  toggleClass: function (b, a) {
    if (OpenLayers.Element.hasClass(b, a)) {
      OpenLayers.Element.removeClass(b, a)
    } else {
      OpenLayers.Element.addClass(b, a)
    }
    return b
  },
  getStyle: function (c, d) {
    c = OpenLayers.Util.getElement(c);
    var e = null;
    if (c && c.style) {
      e = c.style[OpenLayers.String.camelize(d)];
      if (!e) {
        if (document.defaultView && document.defaultView.getComputedStyle) {
          var b = document.defaultView.getComputedStyle(c, null);
          e = b ? b.getPropertyValue(d)  : null
        } else {
          if (c.currentStyle) {
            e = c.currentStyle[OpenLayers.String.camelize(d)]
          }
        }
      }
      var a = [
        'left',
        'top',
        'right',
        'bottom'
      ];
      if (window.opera && (OpenLayers.Util.indexOf(a, d) != - 1) && (OpenLayers.Element.getStyle(c, 'position') == 'static')) {
        e = 'auto'
      }
    }
    return e == 'auto' ? null : e
  }
}; OpenLayers.LonLat = OpenLayers.Class({
  lon: 0,
  lat: 0,
  initialize: function (b, a) {
    if (OpenLayers.Util.isArray(b)) {
      a = b[1];
      b = b[0]
    }
    this.lon = OpenLayers.Util.toFloat(b);
    this.lat = OpenLayers.Util.toFloat(a)
  },
  toString: function () {
    return ('lon=' + this.lon + ',lat=' + this.lat)
  },
  toShortString: function () {
    return (this.lon + ', ' + this.lat)
  },
  clone: function () {
    return new OpenLayers.LonLat(this.lon, this.lat)
  },
  add: function (b, a) {
    if ((b == null) || (a == null)) {
      throw new TypeError('LonLat.add cannot receive null values')
    }
    return new OpenLayers.LonLat(this.lon + OpenLayers.Util.toFloat(b), this.lat + OpenLayers.Util.toFloat(a))
  },
  equals: function (b) {
    var a = false;
    if (b != null) {
      a = ((this.lon == b.lon && this.lat == b.lat) || (isNaN(this.lon) && isNaN(this.lat) && isNaN(b.lon) && isNaN(b.lat)))
    }
    return a
  },
  transform: function (c, b) {
    var a = OpenLayers.Projection.transform({
      x: this.lon,
      y: this.lat
    }, c, b);
    this.lon = a.x;
    this.lat = a.y;
    return this
  },
  wrapDateLine: function (a) {
    var b = this.clone();
    if (a) {
      while (b.lon < a.left) {
        b.lon += a.getWidth()
      }
      while (b.lon > a.right) {
        b.lon -= a.getWidth()
      }
    }
    return b
  },
  CLASS_NAME: 'OpenLayers.LonLat'
}); OpenLayers.LonLat.fromString = function (b) {
  var a = b.split(',');
  return new OpenLayers.LonLat(a[0], a[1])
}; OpenLayers.LonLat.fromArray = function (a) {
  var b = OpenLayers.Util.isArray(a),
  d = b && a[0],
  c = b && a[1];
  return new OpenLayers.LonLat(d, c)
}; OpenLayers.Pixel = OpenLayers.Class({
  x: 0,
  y: 0,
  initialize: function (a, b) {
    this.x = parseFloat(a);
    this.y = parseFloat(b)
  },
  toString: function () {
    return ('x=' + this.x + ',y=' + this.y)
  },
  clone: function () {
    return new OpenLayers.Pixel(this.x, this.y)
  },
  equals: function (a) {
    var b = false;
    if (a != null) {
      b = ((this.x == a.x && this.y == a.y) || (isNaN(this.x) && isNaN(this.y) && isNaN(a.x) && isNaN(a.y)))
    }
    return b
  },
  distanceTo: function (a) {
    return Math.sqrt(Math.pow(this.x - a.x, 2) + Math.pow(this.y - a.y, 2))
  },
  add: function (a, b) {
    if ((a == null) || (b == null)) {
      throw new TypeError('Pixel.add cannot receive null values')
    }
    return new OpenLayers.Pixel(this.x + a, this.y + b)
  },
  offset: function (a) {
    var b = this.clone();
    if (a) {
      b = this.add(a.x, a.y)
    }
    return b
  },
  CLASS_NAME: 'OpenLayers.Pixel'
}); OpenLayers.Size = OpenLayers.Class({
  w: 0,
  h: 0,
  initialize: function (a, b) {
    this.w = parseFloat(a);
    this.h = parseFloat(b)
  },
  toString: function () {
    return ('w=' + this.w + ',h=' + this.h)
  },
  clone: function () {
    return new OpenLayers.Size(this.w, this.h)
  },
  equals: function (b) {
    var a = false;
    if (b != null) {
      a = ((this.w == b.w && this.h == b.h) || (isNaN(this.w) && isNaN(this.h) && isNaN(b.w) && isNaN(b.h)))
    }
    return a
  },
  CLASS_NAME: 'OpenLayers.Size'
}); OpenLayers.String = {
  startsWith: function (b, a) {
    return (b.indexOf(a) == 0)
  },
  contains: function (b, a) {
    return (b.indexOf(a) != - 1)
  },
  trim: function (a) {
    return a.replace(/^\s\s*/, '').replace(/\s\s*$/, '')
  },
  camelize: function (f) {
    var d = f.split('-');
    var b = d[0];
    for (var c = 1, a = d.length; c < a; c++) {
      var e = d[c];
      b += e.charAt(0).toUpperCase() + e.substring(1)
    }
    return b
  },
  format: function (d, c, a) {
    if (!c) {
      c = window
    }
    var b = function (j, e) {
      var h;
      var g = e.split(/\.+/);
      for (var f = 0; f < g.length; f++) {
        if (f == 0) {
          h = c
        }
        if (h === undefined) {
          break
        }
        h = h[g[f]]
      }
      if (typeof h == 'function') {
        h = a ? h.apply(null, a)  : h()
      }
      if (typeof h == 'undefined') {
        return 'undefined'
      } else {
        return h
      }
    };
    return d.replace(OpenLayers.String.tokenRegEx, b)
  },
  tokenRegEx: /\$\{([\w.]+?)\}/g,
  numberRegEx: /^([+-]?)(?=\d|\.\d)\d*(\.\d*)?([Ee]([+-]?\d+))?$/,
  isNumeric: function (a) {
    return OpenLayers.String.numberRegEx.test(a)
  },
  numericIf: function (b, c) {
    var a = b;
    if (c === true && b != null && b.replace) {
      b = b.replace(/^\s*|\s*$/g, '')
    }
    return OpenLayers.String.isNumeric(b) ? parseFloat(b)  : a
  }
}; OpenLayers.Number = {
  decimalSeparator: '.',
  thousandsSeparator: ',',
  limitSigDigs: function (a, c) {
    var b = 0;
    if (c > 0) {
      b = parseFloat(a.toPrecision(c))
    }
    return b
  },
  format: function (c, a, g, i) {
    a = (typeof a != 'undefined') ? a : 0;
    g = (typeof g != 'undefined') ? g : OpenLayers.Number.thousandsSeparator;
    i = (typeof i != 'undefined') ? i : OpenLayers.Number.decimalSeparator;
    if (a != null) {
      c = parseFloat(c.toFixed(a))
    }
    var b = c.toString().split('.');
    if (b.length == 1 && a == null) {
      a = 0
    }
    var d = b[0];
    if (g) {
      var e = /(-?[0-9]+)([0-9]{3})/;
      while (e.test(d)) {
        d = d.replace(e, '$1' + g + '$2')
      }
    }
    var f;
    if (a == 0) {
      f = d
    } else {
      var h = b.length > 1 ? b[1] : '0';
      if (a != null) {
        h = h + new Array(a - h.length + 1).join('0')
      }
      f = d + i + h
    }
    return f
  },
  zeroPad: function (b, a, c) {
    var d = b.toString(c || 10);
    while (d.length < a) {
      d = '0' + d
    }
    return d
  }
}; OpenLayers.Function = {
  bind: function (c, b) {
    var a = Array.prototype.slice.apply(arguments, [
      2
    ]);
    return function () {
      var d = a.concat(Array.prototype.slice.apply(arguments, [
        0
      ]));
      return c.apply(b, d)
    }
  },
  bindAsEventListener: function (b, a) {
    return function (c) {
      return b.call(a, c || window.event)
    }
  },
  False: function () {
    return false
  },
  True: function () {
    return true
  },
  Void: function () {
  }
}; OpenLayers.Array = {
  filter: function (g, f, b) {
    var d = [
    ];
    if (Array.prototype.filter) {
      d = g.filter(f, b)
    } else {
      var a = g.length;
      if (typeof f != 'function') {
        throw new TypeError()
      }
      for (var c = 0; c < a; c++) {
        if (c in g) {
          var e = g[c];
          if (f.call(b, e, c, g)) {
            d.push(e)
          }
        }
      }
    }
    return d
  }
}; OpenLayers.Console = {
  log: function () {
  },
  debug: function () {
  },
  info: function () {
  },
  warn: function () {
  },
  error: function () {
  },
  userError: function (a) {
    alert(a)
  },
  assert: function () {
  },
  dir: function () {
  },
  dirxml: function () {
  },
  trace: function () {
  },
  group: function () {
  },
  groupEnd: function () {
  },
  time: function () {
  },
  timeEnd: function () {
  },
  profile: function () {
  },
  profileEnd: function () {
  },
  count: function () {
  },
  CLASS_NAME: 'OpenLayers.Console'
}; (function () {
  var b = document.getElementsByTagName('script');
  for (var c = 0, a = b.length; c < a; ++c) {
    if (b[c].src.indexOf('firebug.js') != - 1) {
      if (console) {
        OpenLayers.Util.extend(OpenLayers.Console, console);
        break
      }
    }
  }
}) (); OpenLayers.Lang = {
  code: null,
  defaultCode: 'en',
  getCode: function () {
    if (!OpenLayers.Lang.code) {
      OpenLayers.Lang.setCode()
    }
    return OpenLayers.Lang.code
  },
  setCode: function (b) {
    var d;
    if (!b) {
      b = (OpenLayers.BROWSER_NAME == 'msie') ? navigator.userLanguage : navigator.language
    }
    var c = b.split('-');
    c[0] = c[0].toLowerCase();
    if (typeof OpenLayers.Lang[c[0]] == 'object') {
      d = c[0]
    }
    if (c[1]) {
      var a = c[0] + '-' + c[1].toUpperCase();
      if (typeof OpenLayers.Lang[a] == 'object') {
        d = a
      }
    }
    if (!d) {
      OpenLayers.Console.warn('Failed to find OpenLayers.Lang.' + c.join('-') + ' dictionary, falling back to default language');
      d = OpenLayers.Lang.defaultCode
    }
    OpenLayers.Lang.code = d
  },
  translate: function (b, a) {
    var d = OpenLayers.Lang[OpenLayers.Lang.getCode()];
    var c = d && d[b];
    if (!c) {
      c = b
    }
    if (a) {
      c = OpenLayers.String.format(c, a)
    }
    return c
  }
}; OpenLayers.i18n = OpenLayers.Lang.translate; OpenLayers.Event = {
  observers: false,
  KEY_SPACE: 32,
  KEY_BACKSPACE: 8,
  KEY_TAB: 9,
  KEY_RETURN: 13,
  KEY_ESC: 27,
  KEY_LEFT: 37,
  KEY_UP: 38,
  KEY_RIGHT: 39,
  KEY_DOWN: 40,
  KEY_DELETE: 46,
  element: function (a) {
    return a.target || a.srcElement
  },
  isSingleTouch: function (a) {
    return a.touches && a.touches.length == 1
  },
  isMultiTouch: function (a) {
    return a.touches && a.touches.length > 1
  },
  isLeftClick: function (a) {
    return (((a.which) && (a.which == 1)) || ((a.button) && (a.button == 1)))
  },
  isRightClick: function (a) {
    return (((a.which) && (a.which == 3)) || ((a.button) && (a.button == 2)))
  },
  stop: function (b, a) {
    if (!a) {
      OpenLayers.Event.preventDefault(b)
    }
    if (b.stopPropagation) {
      b.stopPropagation()
    } else {
      b.cancelBubble = true
    }
  },
  preventDefault: function (a) {
    if (a.preventDefault) {
      a.preventDefault()
    } else {
      a.returnValue = false
    }
  },
  findElement: function (c, b) {
    var a = OpenLayers.Event.element(c);
    while (a.parentNode && (!a.tagName || (a.tagName.toUpperCase() != b.toUpperCase()))) {
      a = a.parentNode
    }
    return a
  },
  observe: function (b, d, c, a) {
    var e = OpenLayers.Util.getElement(b);
    a = a || false;
    if (d == 'keypress' && (navigator.appVersion.match(/Konqueror|Safari|KHTML/) || e.attachEvent)) {
      d = 'keydown'
    }
    if (!this.observers) {
      this.observers = {
      }
    }
    if (!e._eventCacheID) {
      var f = 'eventCacheID_';
      if (e.id) {
        f = e.id + '_' + f
      }
      e._eventCacheID = OpenLayers.Util.createUniqueID(f)
    }
    var g = e._eventCacheID;
    if (!this.observers[g]) {
      this.observers[g] = [
      ]
    }
    this.observers[g].push({
      element: e,
      name: d,
      observer: c,
      useCapture: a
    });
    if (e.addEventListener) {
      e.addEventListener(d, c, a)
    } else {
      if (e.attachEvent) {
        e.attachEvent('on' + d, c)
      }
    }
  },
  stopObservingElement: function (a) {
    var b = OpenLayers.Util.getElement(a);
    var c = b._eventCacheID;
    this._removeElementObservers(OpenLayers.Event.observers[c])
  },
  _removeElementObservers: function (c) {
    if (c) {
      for (var a = c.length - 1; a >= 0; a--) {
        var b = c[a];
        OpenLayers.Event.stopObserving.apply(this, [
          b.element,
          b.name,
          b.observer,
          b.useCapture
        ])
      }
    }
  },
  stopObserving: function (h, a, g, b) {
    b = b || false;
    var f = OpenLayers.Util.getElement(h);
    var d = f._eventCacheID;
    if (a == 'keypress') {
      if (navigator.appVersion.match(/Konqueror|Safari|KHTML/) || f.detachEvent) {
        a = 'keydown'
      }
    }
    var k = false;
    var c = OpenLayers.Event.observers[d];
    if (c) {
      var e = 0;
      while (!k && e < c.length) {
        var j = c[e];
        if ((j.name == a) && (j.observer == g) && (j.useCapture == b)) {
          c.splice(e, 1);
          if (c.length == 0) {
            delete OpenLayers.Event.observers[d]
          }
          k = true;
          break
        }
        e++
      }
    }
    if (k) {
      if (f.removeEventListener) {
        f.removeEventListener(a, g, b)
      } else {
        if (f && f.detachEvent) {
          f.detachEvent('on' + a, g)
        }
      }
    }
    return k
  },
  unloadCache: function () {
    if (OpenLayers.Event && OpenLayers.Event.observers) {
      for (var a in OpenLayers.Event.observers) {
        var b = OpenLayers.Event.observers[a];
        OpenLayers.Event._removeElementObservers.apply(this, [
          b
        ])
      }
      OpenLayers.Event.observers = false
    }
  },
  CLASS_NAME: 'OpenLayers.Event'
}; OpenLayers.Event.observe(window, 'unload', OpenLayers.Event.unloadCache, false); OpenLayers.Events = OpenLayers.Class({
  BROWSER_EVENTS: [
    'mouseover',
    'mouseout',
    'mousedown',
    'mouseup',
    'mousemove',
    'click',
    'dblclick',
    'rightclick',
    'dblrightclick',
    'resize',
    'focus',
    'blur',
    'touchstart',
    'touchmove',
    'touchend',
    'keydown'
  ],
  listeners: null,
  object: null,
  element: null,
  eventHandler: null,
  fallThrough: null,
  includeXY: false,
  extensions: null,
  extensionCount: null,
  clearMouseListener: null,
  initialize: function (b, c, e, d, a) {
    OpenLayers.Util.extend(this, a);
    this.object = b;
    this.fallThrough = d;
    this.listeners = {
    };
    this.extensions = {
    };
    this.extensionCount = {
    };
    this._msTouches = [
    ];
    if (c != null) {
      this.attachToElement(c)
    }
  },
  destroy: function () {
    for (var a in this.extensions) {
      if (typeof this.extensions[a] !== 'boolean') {
        this.extensions[a].destroy()
      }
    }
    this.extensions = null;
    if (this.element) {
      OpenLayers.Event.stopObservingElement(this.element);
      if (this.element.hasScrollEvent) {
        OpenLayers.Event.stopObserving(window, 'scroll', this.clearMouseListener)
      }
    }
    this.element = null;
    this.listeners = null;
    this.object = null;
    this.fallThrough = null;
    this.eventHandler = null
  },
  addEventType: function (a) {
  },
  attachToElement: function (d) {
    if (this.element) {
      OpenLayers.Event.stopObservingElement(this.element)
    } else {
      this.eventHandler = OpenLayers.Function.bindAsEventListener(this.handleBrowserEvent, this);
      this.clearMouseListener = OpenLayers.Function.bind(this.clearMouseCache, this)
    }
    this.element = d;
    var b = !!window.navigator.msMaxTouchPoints;
    var e;
    for (var c = 0, a = this.BROWSER_EVENTS.length; c < a; c++) {
      e = this.BROWSER_EVENTS[c];
      OpenLayers.Event.observe(d, e, this.eventHandler);
      if (b && e.indexOf('touch') === 0) {
        this.addMsTouchListener(d, e, this.eventHandler)
      }
    }
    OpenLayers.Event.observe(d, 'dragstart', OpenLayers.Event.stop)
  },
  on: function (a) {
    for (var b in a) {
      if (b != 'scope' && a.hasOwnProperty(b)) {
        this.register(b, a.scope, a[b])
      }
    }
  },
  register: function (c, f, d, a) {
    if (c in OpenLayers.Events && !this.extensions[c]) {
      this.extensions[c] = new OpenLayers.Events[c](this)
    }
    if (d != null) {
      if (f == null) {
        f = this.object
      }
      var b = this.listeners[c];
      if (!b) {
        b = [
        ];
        this.listeners[c] = b;
        this.extensionCount[c] = 0
      }
      var e = {
        obj: f,
        func: d
      };
      if (a) {
        b.splice(this.extensionCount[c], 0, e);
        if (typeof a === 'object' && a.extension) {
          this.extensionCount[c]++
        }
      } else {
        b.push(e)
      }
    }
  },
  registerPriority: function (a, c, b) {
    this.register(a, c, b, true)
  },
  un: function (a) {
    for (var b in a) {
      if (b != 'scope' && a.hasOwnProperty(b)) {
        this.unregister(b, a.scope, a[b])
      }
    }
  },
  unregister: function (d, f, e) {
    if (f == null) {
      f = this.object
    }
    var c = this.listeners[d];
    if (c != null) {
      for (var b = 0, a = c.length; b < a; b++) {
        if (c[b].obj == f && c[b].func == e) {
          c.splice(b, 1);
          break
        }
      }
    }
  },
  remove: function (a) {
    if (this.listeners[a] != null) {
      this.listeners[a] = [
      ]
    }
  },
  triggerEvent: function (e, b) {
    var d = this.listeners[e];
    if (!d || d.length == 0) {
      return undefined
    }
    if (b == null) {
      b = {
      }
    }
    b.object = this.object;
    b.element = this.element;
    if (!b.type) {
      b.type = e
    }
    d = d.slice();
    var f;
    for (var c = 0, a = d.length; c < a; c++) {
      var g = d[c];
      f = g.func.apply(g.obj, [
        b
      ]);
      if ((f != undefined) && (f == false)) {
        break
      }
    }
    if (!this.fallThrough) {
      OpenLayers.Event.stop(b, true)
    }
    return f
  },
  handleBrowserEvent: function (j) {
    var e = j.type,
    f = this.listeners[e];
    if (!f || f.length == 0) {
      return
    }
    var c = j.touches;
    if (c && c[0]) {
      var h = 0;
      var g = 0;
      var d = c.length;
      var b;
      for (var a = 0; a < d; ++a) {
        b = this.getTouchClientXY(c[a]);
        h += b.clientX;
        g += b.clientY
      }
      j.clientX = h / d;
      j.clientY = g / d
    }
    if (this.includeXY) {
      j.xy = this.getMousePosition(j)
    }
    this.triggerEvent(e, j)
  },
  getTouchClientXY: function (d) {
    var e = window.olMockWin || window,
    c = e.pageXOffset,
    b = e.pageYOffset,
    a = d.clientX,
    f = d.clientY;
    if (d.pageY === 0 && Math.floor(f) > Math.floor(d.pageY) || d.pageX === 0 && Math.floor(a) > Math.floor(d.pageX)) {
      a = a - c;
      f = f - b
    } else {
      if (f < (d.pageY - b) || a < (d.pageX - c)) {
        a = d.pageX - c;
        f = d.pageY - b
      }
    }
    d.olClientX = a;
    d.olClientY = f;
    return {
      clientX: a,
      clientY: f
    }
  },
  clearMouseCache: function () {
    this.element.scrolls = null;
    this.element.lefttop = null;
    this.element.offsets = null
  },
  getMousePosition: function (a) {
    if (!this.includeXY) {
      this.clearMouseCache()
    } else {
      if (!this.element.hasScrollEvent) {
        OpenLayers.Event.observe(window, 'scroll', this.clearMouseListener);
        this.element.hasScrollEvent = true
      }
    }
    if (!this.element.scrolls) {
      var b = OpenLayers.Util.getViewportElement();
      this.element.scrolls = [
        window.pageXOffset || b.scrollLeft,
        window.pageYOffset || b.scrollTop
      ]
    }
    if (!this.element.lefttop) {
      this.element.lefttop = [
        (document.documentElement.clientLeft || 0),
        (document.documentElement.clientTop || 0)
      ]
    }
    if (!this.element.offsets) {
      this.element.offsets = OpenLayers.Util.pagePosition(this.element)
    }
    return new OpenLayers.Pixel((a.clientX + this.element.scrolls[0]) - this.element.offsets[0] - this.element.lefttop[0], (a.clientY + this.element.scrolls[1]) - this.element.offsets[1] - this.element.lefttop[1])
  },
  addMsTouchListener: function (b, e, d) {
    var a = this.eventHandler;
    var f = this._msTouches;
    function c(g) {
      d(OpenLayers.Util.applyDefaults({
        stopPropagation: function () {
          for (var h = f.length - 1; h >= 0; --h) {
            f[h].stopPropagation()
          }
        },
        preventDefault: function () {
          for (var h = f.length - 1; h >= 0; --h) {
            f[h].preventDefault()
          }
        },
        type: e
      }, g))
    }
    switch (e) {
      case 'touchstart':
        return this.addMsTouchListenerStart(b, e, c);
      case 'touchend':
        return this.addMsTouchListenerEnd(b, e, c);
      case 'touchmove':
        return this.addMsTouchListenerMove(b, e, c);
      default:
        throw 'Unknown touch event type'
    }
  },
  addMsTouchListenerStart: function (c, e, d) {
    var f = this._msTouches;
    var a = function (k) {
      var h = false;
      for (var g = 0, j = f.length; g < j; ++g) {
        if (f[g].pointerId == k.pointerId) {
          h = true;
          break
        }
      }
      if (!h) {
        f.push(k)
      }
      k.touches = f.slice();
      d(k)
    };
    OpenLayers.Event.observe(c, 'MSPointerDown', a);
    var b = function (j) {
      for (var g = 0, h = f.length; g < h; ++g) {
        if (f[g].pointerId == j.pointerId) {
          f.splice(g, 1);
          break
        }
      }
    };
    OpenLayers.Event.observe(c, 'MSPointerUp', b)
  },
  addMsTouchListenerMove: function (b, d, c) {
    var e = this._msTouches;
    var a = function (h) {
      if (h.pointerType == h.MSPOINTER_TYPE_MOUSE && h.buttons == 0) {
        return
      }
      if (e.length == 1 && e[0].pageX == h.pageX && e[0].pageY == h.pageY) {
        return
      }
      for (var f = 0, g = e.length; f < g; ++f) {
        if (e[f].pointerId == h.pointerId) {
          e[f] = h;
          break
        }
      }
      h.touches = e.slice();
      c(h)
    };
    OpenLayers.Event.observe(b, 'MSPointerMove', a)
  },
  addMsTouchListenerEnd: function (b, d, c) {
    var e = this._msTouches;
    var a = function (h) {
      for (var f = 0, g = e.length; f < g; ++f) {
        if (e[f].pointerId == h.pointerId) {
          e.splice(f, 1);
          break
        }
      }
      h.touches = e.slice();
      c(h)
    };
    OpenLayers.Event.observe(b, 'MSPointerUp', a)
  },
  CLASS_NAME: 'OpenLayers.Events'
}); OpenLayers.Events.buttonclick = OpenLayers.Class({
  target: null,
  events: [
    'mousedown',
    'mouseup',
    'click',
    'dblclick',
    'touchstart',
    'touchmove',
    'touchend',
    'keydown'
  ],
  startRegEx: /^mousedown|touchstart$/,
  cancelRegEx: /^touchmove$/,
  completeRegEx: /^mouseup|touchend$/,
  initialize: function (b) {
    this.target = b;
    for (var a = this.events.length - 1; a >= 0; --a) {
      this.target.register(this.events[a], this, this.buttonClick, {
        extension: true
      })
    }
  },
  destroy: function () {
    for (var a = this.events.length - 1; a >= 0; --a) {
      this.target.unregister(this.events[a], this, this.buttonClick)
    }
    delete this.target
  },
  getPressedButton: function (b) {
    var c = 3,
    a;
    do {
      if (OpenLayers.Element.hasClass(b, 'olButton')) {
        a = b;
        break
      }
      b = b.parentNode
    } while (--c > 0 && b);
    return a
  },
  ignore: function (a) {
    var b = 3,
    c = false;
    do {
      if (a.nodeName.toLowerCase() === 'a') {
        c = true;
        break
      }
      a = a.parentNode
    } while (--b > 0 && a);
    return c
  },
  buttonClick: function (b) {
    var a = true,
    e = OpenLayers.Event.element(b);
    if (e && (OpenLayers.Event.isLeftClick(b) || !~b.type.indexOf('mouse'))) {
      var d = this.getPressedButton(e);
      if (d) {
        if (b.type === 'keydown') {
          switch (b.keyCode) {
            case OpenLayers.Event.KEY_RETURN:
            case OpenLayers.Event.KEY_SPACE:
              this.target.triggerEvent('buttonclick', {
                buttonElement: d
              });
              OpenLayers.Event.stop(b);
              a = false;
              break
          }
        } else {
          if (this.startEvt) {
            if (this.completeRegEx.test(b.type)) {
              var h = OpenLayers.Util.pagePosition(d);
              var c = OpenLayers.Util.getViewportElement();
              var f = window.pageYOffset || c.scrollTop;
              var g = window.pageXOffset || c.scrollLeft;
              h[0] = h[0] - g;
              h[1] = h[1] - f;
              this.target.triggerEvent('buttonclick', {
                buttonElement: d,
                buttonXY: {
                  x: this.startEvt.clientX - h[0],
                  y: this.startEvt.clientY - h[1]
                }
              })
            }
            if (this.cancelRegEx.test(b.type)) {
              delete this.startEvt
            }
            OpenLayers.Event.stop(b);
            a = false
          }
        }
        if (this.startRegEx.test(b.type)) {
          this.startEvt = b;
          OpenLayers.Event.stop(b);
          a = false
        }
      } else {
        a = !this.ignore(OpenLayers.Event.element(b));
        delete this.startEvt
      }
    }
    return a
  }
}); OpenLayers.Control = OpenLayers.Class({
  id: null,
  map: null,
  div: null,
  type: null,
  allowSelection: false,
  displayClass: '',
  title: '',
  autoActivate: false,
  active: null,
  handlerOptions: null,
  handler: null,
  eventListeners: null,
  events: null,
  initialize: function (a) {
    this.displayClass = this.CLASS_NAME.replace('OpenLayers.', 'ol').replace(/\./g, '');
    OpenLayers.Util.extend(this, a);
    this.events = new OpenLayers.Events(this);
    if (this.eventListeners instanceof Object) {
      this.events.on(this.eventListeners)
    }
    if (this.id == null) {
      this.id = OpenLayers.Util.createUniqueID(this.CLASS_NAME + '_')
    }
  },
  destroy: function () {
    if (this.events) {
      if (this.eventListeners) {
        this.events.un(this.eventListeners)
      }
      this.events.destroy();
      this.events = null
    }
    this.eventListeners = null;
    if (this.handler) {
      this.handler.destroy();
      this.handler = null
    }
    if (this.handlers) {
      for (var a in this.handlers) {
        if (this.handlers.hasOwnProperty(a) && typeof this.handlers[a].destroy == 'function') {
          this.handlers[a].destroy()
        }
      }
      this.handlers = null
    }
    if (this.map) {
      this.map.removeControl(this);
      this.map = null
    }
    this.div = null
  },
  setMap: function (a) {
    this.map = a;
    if (this.handler) {
      this.handler.setMap(a)
    }
  },
  draw: function (a) {
    if (this.div == null) {
      this.div = OpenLayers.Util.createDiv(this.id);
      this.div.className = this.displayClass;
      if (!this.allowSelection) {
        this.div.className += ' olControlNoSelect';
        this.div.setAttribute('unselectable', 'on', 0);
        this.div.onselectstart = OpenLayers.Function.False
      }
      if (this.title != '') {
        this.div.title = this.title
      }
    }
    if (a != null) {
      this.position = a.clone()
    }
    this.moveTo(this.position);
    return this.div
  },
  moveTo: function (a) {
    if ((a != null) && (this.div != null)) {
      this.div.style.left = a.x + 'px';
      this.div.style.top = a.y + 'px'
    }
  },
  activate: function () {
    if (this.active) {
      return false
    }
    if (this.handler) {
      this.handler.activate()
    }
    this.active = true;
    if (this.map) {
      OpenLayers.Element.addClass(this.map.viewPortDiv, this.displayClass.replace(/ /g, '') + 'Active')
    }
    this.events.triggerEvent('activate');
    return true
  },
  deactivate: function () {
    if (this.active) {
      if (this.handler) {
        this.handler.deactivate()
      }
      this.active = false;
      if (this.map) {
        OpenLayers.Element.removeClass(this.map.viewPortDiv, this.displayClass.replace(/ /g, '') + 'Active')
      }
      this.events.triggerEvent('deactivate');
      return true
    }
    return false
  },
  CLASS_NAME: 'OpenLayers.Control'
}); OpenLayers.Control.TYPE_BUTTON = 1; OpenLayers.Control.TYPE_TOGGLE = 2; OpenLayers.Control.TYPE_TOOL = 3;
OpenLayers.Handler = OpenLayers.Class({
  id: null,
  control: null,
  map: null,
  keyMask: null,
  active: false,
  evt: null,
  touch: false,
  initialize: function (d, b, a) {
    OpenLayers.Util.extend(this, a);
    this.control = d;
    this.callbacks = b;
    var c = this.map || d.map;
    if (c) {
      this.setMap(c)
    }
    this.id = OpenLayers.Util.createUniqueID(this.CLASS_NAME + '_')
  },
  setMap: function (a) {
    this.map = a
  },
  checkModifiers: function (a) {
    if (this.keyMask == null) {
      return true
    }
    var b = (a.shiftKey ? OpenLayers.Handler.MOD_SHIFT : 0) | (a.ctrlKey ? OpenLayers.Handler.MOD_CTRL : 0) | (a.altKey ? OpenLayers.Handler.MOD_ALT : 0) | (a.metaKey ? OpenLayers.Handler.MOD_META : 0);
    return (b == this.keyMask)
  },
  activate: function () {
    if (this.active) {
      return false
    }
    var c = OpenLayers.Events.prototype.BROWSER_EVENTS;
    for (var b = 0, a = c.length; b < a; b++) {
      if (this[c[b]]) {
        this.register(c[b], this[c[b]])
      }
    }
    this.active = true;
    return true
  },
  deactivate: function () {
    if (!this.active) {
      return false
    }
    var c = OpenLayers.Events.prototype.BROWSER_EVENTS;
    for (var b = 0, a = c.length; b < a; b++) {
      if (this[c[b]]) {
        this.unregister(c[b], this[c[b]])
      }
    }
    this.touch = false;
    this.active = false;
    return true
  },
  startTouch: function () {
    if (!this.touch) {
      this.touch = true;
      var c = [
        'mousedown',
        'mouseup',
        'mousemove',
        'click',
        'dblclick',
        'mouseout'
      ];
      for (var b = 0, a = c.length; b < a; b++) {
        if (this[c[b]]) {
          this.unregister(c[b], this[c[b]])
        }
      }
    }
  },
  callback: function (b, a) {
    if (b && this.callbacks[b]) {
      this.callbacks[b].apply(this.control, a)
    }
  },
  register: function (a, b) {
    this.map.events.registerPriority(a, this, b);
    this.map.events.registerPriority(a, this, this.setEvent)
  },
  unregister: function (a, b) {
    this.map.events.unregister(a, this, b);
    this.map.events.unregister(a, this, this.setEvent)
  },
  setEvent: function (a) {
    this.evt = a;
    return true
  },
  destroy: function () {
    this.deactivate();
    this.control = this.map = null
  },
  CLASS_NAME: 'OpenLayers.Handler'
}); OpenLayers.Handler.MOD_NONE = 0; OpenLayers.Handler.MOD_SHIFT = 1; OpenLayers.Handler.MOD_CTRL = 2; OpenLayers.Handler.MOD_ALT = 4; OpenLayers.Handler.MOD_META = 8; OpenLayers.Handler.Box = OpenLayers.Class(OpenLayers.Handler, {
  dragHandler: null,
  boxDivClassName: 'olHandlerBoxZoomBox',
  boxOffsets: null,
  initialize: function (c, b, a) {
    OpenLayers.Handler.prototype.initialize.apply(this, arguments);
    this.dragHandler = new OpenLayers.Handler.Drag(this, {
      down: this.startBox,
      move: this.moveBox,
      out: this.removeBox,
      up: this.endBox
    }, {
      keyMask: this.keyMask
    })
  },
  destroy: function () {
    OpenLayers.Handler.prototype.destroy.apply(this, arguments);
    if (this.dragHandler) {
      this.dragHandler.destroy();
      this.dragHandler = null
    }
  },
  setMap: function (a) {
    OpenLayers.Handler.prototype.setMap.apply(this, arguments);
    if (this.dragHandler) {
      this.dragHandler.setMap(a)
    }
  },
  startBox: function (a) {
    this.callback('start', [
    ]);
    this.zoomBox = OpenLayers.Util.createDiv('zoomBox', {
      x: - 9999,
      y: - 9999
    });
    this.zoomBox.className = this.boxDivClassName;
    this.zoomBox.style.zIndex = this.map.Z_INDEX_BASE.Popup - 1;
    this.map.viewPortDiv.appendChild(this.zoomBox);
    OpenLayers.Element.addClass(this.map.viewPortDiv, 'olDrawBox')
  },
  moveBox: function (e) {
    var d = this.dragHandler.start.x;
    var b = this.dragHandler.start.y;
    var c = Math.abs(d - e.x);
    var a = Math.abs(b - e.y);
    var f = this.getBoxOffsets();
    this.zoomBox.style.width = (c + f.width + 1) + 'px';
    this.zoomBox.style.height = (a + f.height + 1) + 'px';
    this.zoomBox.style.left = (e.x < d ? d - c - f.left : d - f.left) + 'px';
    this.zoomBox.style.top = (e.y < b ? b - a - f.top : b - f.top) + 'px'
  },
  endBox: function (b) {
    var a;
    if (Math.abs(this.dragHandler.start.x - b.x) > 5 || Math.abs(this.dragHandler.start.y - b.y) > 5) {
      var g = this.dragHandler.start;
      var f = Math.min(g.y, b.y);
      var c = Math.max(g.y, b.y);
      var e = Math.min(g.x, b.x);
      var d = Math.max(g.x, b.x);
      a = new OpenLayers.Bounds(e, c, d, f)
    } else {
      a = this.dragHandler.start.clone()
    }
    this.removeBox();
    this.callback('done', [
      a
    ])
  },
  removeBox: function () {
    this.map.viewPortDiv.removeChild(this.zoomBox);
    this.zoomBox = null;
    this.boxOffsets = null;
    OpenLayers.Element.removeClass(this.map.viewPortDiv, 'olDrawBox')
  },
  activate: function () {
    if (OpenLayers.Handler.prototype.activate.apply(this, arguments)) {
      this.dragHandler.activate();
      return true
    } else {
      return false
    }
  },
  deactivate: function () {
    if (OpenLayers.Handler.prototype.deactivate.apply(this, arguments)) {
      if (this.dragHandler.deactivate()) {
        if (this.zoomBox) {
          this.removeBox()
        }
      }
      return true
    } else {
      return false
    }
  },
  getBoxOffsets: function () {
    if (!this.boxOffsets) {
      var d = document.createElement('div');
      d.style.position = 'absolute';
      d.style.border = '1px solid black';
      d.style.width = '3px';
      document.body.appendChild(d);
      var a = d.clientWidth == 3;
      document.body.removeChild(d);
      var f = parseInt(OpenLayers.Element.getStyle(this.zoomBox, 'border-left-width'));
      var c = parseInt(OpenLayers.Element.getStyle(this.zoomBox, 'border-right-width'));
      var e = parseInt(OpenLayers.Element.getStyle(this.zoomBox, 'border-top-width'));
      var b = parseInt(OpenLayers.Element.getStyle(this.zoomBox, 'border-bottom-width'));
      this.boxOffsets = {
        left: f,
        right: c,
        top: e,
        bottom: b,
        width: a === false ? f + c : 0,
        height: a === false ? e + b : 0
      }
    }
    return this.boxOffsets
  },
  CLASS_NAME: 'OpenLayers.Handler.Box'
}); OpenLayers.Handler.MouseWheel = OpenLayers.Class(OpenLayers.Handler, {
  wheelListener: null,
  interval: 0,
  maxDelta: Number.POSITIVE_INFINITY,
  delta: 0,
  cumulative: true,
  initialize: function (c, b, a) {
    OpenLayers.Handler.prototype.initialize.apply(this, arguments);
    this.wheelListener = OpenLayers.Function.bindAsEventListener(this.onWheelEvent, this)
  },
  destroy: function () {
    OpenLayers.Handler.prototype.destroy.apply(this, arguments);
    this.wheelListener = null
  },
  onWheelEvent: function (m) {
    if (!this.map || !this.checkModifiers(m)) {
      return
    }
    var h = false;
    var d = false;
    var g = false;
    var b = OpenLayers.Event.element(m);
    while ((b != null) && !g && !h) {
      if (!h) {
        try {
          var c;
          if (b.currentStyle) {
            c = b.currentStyle.overflow
          } else {
            var a = document.defaultView.getComputedStyle(b, null);
            c = a.getPropertyValue('overflow')
          }
          h = (c && (c == 'auto') || (c == 'scroll'))
        } catch (f) {
        }
      }
      if (!d) {
        d = OpenLayers.Element.hasClass(b, 'olScrollable');
        if (!d) {
          for (var j = 0, l = this.map.layers.length;
          j < l; j++) {
            var k = this.map.layers[j];
            if (b == k.div || b == k.pane) {
              d = true;
              break
            }
          }
        }
      }
      g = (b == this.map.div);
      b = b.parentNode
    }
    if (!h && g) {
      if (d) {
        var o = 0;
        if (m.wheelDelta) {
          o = m.wheelDelta;
          if (o % 160 === 0) {
            o = o * 0.75
          }
          o = o / 120
        } else {
          if (m.detail) {
            o = - (m.detail / Math.abs(m.detail))
          }
        }
        this.delta += o;
        window.clearTimeout(this._timeoutId);
        if (this.interval && Math.abs(this.delta) < this.maxDelta) {
          var n = OpenLayers.Util.extend({
          }, m);
          this._timeoutId = window.setTimeout(OpenLayers.Function.bind(function () {
            this.wheelZoom(n)
          }, this), this.interval)
        } else {
          this.wheelZoom(m)
        }
      }
      OpenLayers.Event.stop(m)
    }
  },
  wheelZoom: function (a) {
    var b = this.delta;
    this.delta = 0;
    if (b) {
      a.xy = this.map.events.getMousePosition(a);
      if (b < 0) {
        this.callback('down', [
          a,
          this.cumulative ? Math.max( - this.maxDelta, b)  : - 1
        ])
      } else {
        this.callback('up', [
          a,
          this.cumulative ? Math.min(this.maxDelta, b)  : 1
        ])
      }
    }
  },
  activate: function (a) {
    if (OpenLayers.Handler.prototype.activate.apply(this, arguments)) {
      var b = this.wheelListener;
      OpenLayers.Event.observe(window, 'DOMMouseScroll', b);
      OpenLayers.Event.observe(window, 'mousewheel', b);
      OpenLayers.Event.observe(document, 'mousewheel', b);
      return true
    } else {
      return false
    }
  },
  deactivate: function (a) {
    if (OpenLayers.Handler.prototype.deactivate.apply(this, arguments)) {
      var b = this.wheelListener;
      OpenLayers.Event.stopObserving(window, 'DOMMouseScroll', b);
      OpenLayers.Event.stopObserving(window, 'mousewheel', b);
      OpenLayers.Event.stopObserving(document, 'mousewheel', b);
      return true
    } else {
      return false
    }
  },
  CLASS_NAME: 'OpenLayers.Handler.MouseWheel'
}); OpenLayers.Handler.Click = OpenLayers.Class(OpenLayers.Handler, {
  delay: 300,
  single: true,
  'double': false,
  pixelTolerance: 0,
  dblclickTolerance: 13,
  stopSingle: false,
  stopDouble: false,
  timerId: null,
  down: null,
  last: null,
  first: null,
  rightclickTimerId: null,
  touchstart: function (a) {
    this.startTouch();
    this.down = this.getEventInfo(a);
    this.last = this.getEventInfo(a);
    return true
  },
  touchmove: function (a) {
    this.last = this.getEventInfo(a);
    return true
  },
  touchend: function (a) {
    if (this.down) {
      a.xy = this.last.xy;
      a.lastTouches = this.last.touches;
      this.handleSingle(a);
      this.down = null
    }
    return true
  },
  mousedown: function (a) {
    this.down = this.getEventInfo(a);
    this.last = this.getEventInfo(a);
    return true
  },
  mouseup: function (b) {
    var a = true;
    if (this.checkModifiers(b) && this.control.handleRightClicks && OpenLayers.Event.isRightClick(b)) {
      a = this.rightclick(b)
    }
    return a
  },
  rightclick: function (b) {
    if (this.passesTolerance(b)) {
      if (this.rightclickTimerId != null) {
        this.clearTimer();
        this.callback('dblrightclick', [
          b
        ]);
        return !this.stopDouble
      } else {
        var a = this['double'] ? OpenLayers.Util.extend({
        }, b)  : this.callback('rightclick', [
          b
        ]);
        var c = OpenLayers.Function.bind(this.delayedRightCall, this, a);
        this.rightclickTimerId = window.setTimeout(c, this.delay)
      }
    }
    return !this.stopSingle
  },
  delayedRightCall: function (a) {
    this.rightclickTimerId = null;
    if (a) {
      this.callback('rightclick', [
        a
      ])
    }
  },
  click: function (a) {
    if (!this.last) {
      this.last = this.getEventInfo(a)
    }
    this.handleSingle(a);
    return !this.stopSingle
  },
  dblclick: function (a) {
    this.handleDouble(a);
    return !this.stopDouble
  },
  handleDouble: function (a) {
    if (this.passesDblclickTolerance(a)) {
      if (this['double']) {
        this.callback('dblclick', [
          a
        ])
      }
      this.clearTimer()
    }
  },
  handleSingle: function (b) {
    if (this.passesTolerance(b)) {
      if (this.timerId != null) {
        if (this.last.touches && this.last.touches.length === 1) {
          if (this['double']) {
            OpenLayers.Event.preventDefault(b)
          }
          this.handleDouble(b)
        }
        if (!this.last.touches || this.last.touches.length !== 2) {
          this.clearTimer()
        }
      } else {
        this.first = this.getEventInfo(b);
        var a = this.single ? OpenLayers.Util.extend({
        }, b)  : null;
        this.queuePotentialClick(a)
      }
    }
  },
  queuePotentialClick: function (a) {
    this.timerId = window.setTimeout(OpenLayers.Function.bind(this.delayedCall, this, a), this.delay)
  },
  passesTolerance: function (a) {
    var d = true;
    if (this.pixelTolerance != null && this.down && this.down.xy) {
      d = this.pixelTolerance >= this.down.xy.distanceTo(a.xy);
      if (d && this.touch && this.down.touches.length === this.last.touches.length) {
        for (var b = 0, c = this.down.touches.length; b < c; ++b) {
          if (this.getTouchDistance(this.down.touches[b], this.last.touches[b]) > this.pixelTolerance) {
            d = false;
            break
          }
        }
      }
    }
    return d
  },
  getTouchDistance: function (b, a) {
    return Math.sqrt(Math.pow(b.clientX - a.clientX, 2) + Math.pow(b.clientY - a.clientY, 2))
  },
  passesDblclickTolerance: function (a) {
    var b = true;
    if (this.down && this.first) {
      b = this.down.xy.distanceTo(this.first.xy) <= this.dblclickTolerance
    }
    return b
  },
  clearTimer: function () {
    if (this.timerId != null) {
      window.clearTimeout(this.timerId);
      this.timerId = null
    }
    if (this.rightclickTimerId != null) {
      window.clearTimeout(this.rightclickTimerId);
      this.rightclickTimerId = null
    }
  },
  delayedCall: function (a) {
    this.timerId = null;
    if (a) {
      this.callback('click', [
        a
      ])
    }
  },
  getEventInfo: function (b) {
    var d;
    if (b.touches) {
      var a = b.touches.length;
      d = new Array(a);
      var e;
      for (var c = 0; c < a; c++) {
        e = b.touches[c];
        d[c] = {
          clientX: e.olClientX,
          clientY: e.olClientY
        }
      }
    }
    return {
      xy: b.xy,
      touches: d
    }
  },
  deactivate: function () {
    var a = false;
    if (OpenLayers.Handler.prototype.deactivate.apply(this, arguments)) {
      this.clearTimer();
      this.down = null;
      this.first = null;
      this.last = null;
      a = true
    }
    return a
  },
  CLASS_NAME: 'OpenLayers.Handler.Click'
}); OpenLayers.Handler.Drag = OpenLayers.Class(OpenLayers.Handler, {
  started: false,
  stopDown: true,
  dragging: false,
  last: null,
  start: null,
  lastMoveEvt: null,
  oldOnselectstart: null,
  interval: 0,
  timeoutId: null,
  documentDrag: false,
  documentEvents: null,
  initialize: function (d, c, a) {
    OpenLayers.Handler.prototype.initialize.apply(this, arguments);
    if (this.documentDrag === true) {
      var b = this;
      this._docMove = function (e) {
        b.mousemove({
          xy: {
            x: e.clientX,
            y: e.clientY
          },
          element: document
        })
      };
      this._docUp = function (e) {
        b.mouseup({
          xy: {
            x: e.clientX,
            y: e.clientY
          }
        })
      }
    }
  },
  dragstart: function (b) {
    var a = true;
    this.dragging = false;
    if (this.checkModifiers(b) && (OpenLayers.Event.isLeftClick(b) || OpenLayers.Event.isSingleTouch(b))) {
      this.started = true;
      this.start = b.xy;
      this.last = b.xy;
      OpenLayers.Element.addClass(this.map.viewPortDiv, 'olDragDown');
      this.down(b);
      this.callback('down', [
        b.xy
      ]);
      OpenLayers.Event.preventDefault(b);
      if (!this.oldOnselectstart) {
        this.oldOnselectstart = document.onselectstart ? document.onselectstart : OpenLayers.Function.True
      }
      document.onselectstart = OpenLayers.Function.False;
      a = !this.stopDown
    } else {
      this.started = false;
      this.start = null;
      this.last = null
    }
    return a
  },
  dragmove: function (a) {
    this.lastMoveEvt = a;
    if (this.started && !this.timeoutId && (a.xy.x != this.last.x || a.xy.y != this.last.y)) {
      if (this.documentDrag === true && this.documentEvents) {
        if (a.element === document) {
          this.adjustXY(a);
          this.setEvent(a)
        } else {
          this.removeDocumentEvents()
        }
      }
      if (this.interval > 0) {
        this.timeoutId = setTimeout(OpenLayers.Function.bind(this.removeTimeout, this), this.interval)
      }
      this.dragging = true;
      this.move(a);
      this.callback('move', [
        a.xy
      ]);
      if (!this.oldOnselectstart) {
        this.oldOnselectstart = document.onselectstart;
        document.onselectstart = OpenLayers.Function.False
      }
      this.last = a.xy
    }
    return true
  },
  dragend: function (b) {
    if (this.started) {
      if (this.documentDrag === true && this.documentEvents) {
        this.adjustXY(b);
        this.removeDocumentEvents()
      }
      var a = (this.start != this.last);
      this.started = false;
      this.dragging = false;
      OpenLayers.Element.removeClass(this.map.viewPortDiv, 'olDragDown');
      this.up(b);
      this.callback('up', [
        b.xy
      ]);
      if (a) {
        this.callback('done', [
          b.xy
        ])
      }
      document.onselectstart = this.oldOnselectstart
    }
    return true
  },
  down: function (a) {
  },
  move: function (a) {
  },
  up: function (a) {
  },
  out: function (a) {
  },
  mousedown: function (a) {
    return this.dragstart(a)
  },
  touchstart: function (a) {
    this.startTouch();
    return this.dragstart(a)
  },
  mousemove: function (a) {
    return this.dragmove(a)
  },
  touchmove: function (a) {
    return this.dragmove(a)
  },
  removeTimeout: function () {
    this.timeoutId = null;
    if (this.dragging) {
      this.mousemove(this.lastMoveEvt)
    }
  },
  mouseup: function (a) {
    return this.dragend(a)
  },
  touchend: function (a) {
    a.xy = this.last;
    return this.dragend(a)
  },
  mouseout: function (b) {
    if (this.started && OpenLayers.Util.mouseLeft(b, this.map.viewPortDiv)) {
      if (this.documentDrag === true) {
        this.addDocumentEvents()
      } else {
        var a = (this.start != this.last);
        this.started = false;
        this.dragging = false;
        OpenLayers.Element.removeClass(this.map.viewPortDiv, 'olDragDown');
        this.out(b);
        this.callback('out', [
        ]);
        if (a) {
          this.callback('done', [
            b.xy
          ])
        }
        if (document.onselectstart) {
          document.onselectstart = this.oldOnselectstart
        }
      }
    }
    return true
  },
  click: function (a) {
    return (this.start == this.last)
  },
  activate: function () {
    var a = false;
    if (OpenLayers.Handler.prototype.activate.apply(this, arguments)) {
      this.dragging = false;
      a = true
    }
    return a
  },
  deactivate: function () {
    var a = false;
    if (OpenLayers.Handler.prototype.deactivate.apply(this, arguments)) {
      this.started = false;
      this.dragging = false;
      this.start = null;
      this.last = null;
      a = true;
      OpenLayers.Element.removeClass(this.map.viewPortDiv, 'olDragDown')
    }
    return a
  },
  adjustXY: function (a) {
    var b = OpenLayers.Util.pagePosition(this.map.viewPortDiv);
    a.xy.x -= b[0];
    a.xy.y -= b[1]
  },
  addDocumentEvents: function () {
    OpenLayers.Element.addClass(document.body, 'olDragDown');
    this.documentEvents = true;
    OpenLayers.Event.observe(document, 'mousemove', this._docMove);
    OpenLayers.Event.observe(document, 'mouseup', this._docUp)
  },
  removeDocumentEvents: function () {
    OpenLayers.Element.removeClass(document.body, 'olDragDown');
    this.documentEvents = false;
    OpenLayers.Event.stopObserving(document, 'mousemove', this._docMove);
    OpenLayers.Event.stopObserving(document, 'mouseup', this._docUp)
  },
  CLASS_NAME: 'OpenLayers.Handler.Drag'
}); OpenLayers.Control.DragPan = OpenLayers.Class(OpenLayers.Control, {
  type: OpenLayers.Control.TYPE_TOOL,
  panned: false,
  interval: 0,
  documentDrag: false,
  kinetic: null,
  enableKinetic: true,
  kineticInterval: 10,
  draw: function () {
    if (this.enableKinetic && OpenLayers.Kinetic) {
      var a = {
        interval: this.kineticInterval
      };
      if (typeof this.enableKinetic === 'object') {
        a = OpenLayers.Util.extend(a, this.enableKinetic)
      }
      this.kinetic = new OpenLayers.Kinetic(a)
    }
    this.handler = new OpenLayers.Handler.Drag(this, {
      move: this.panMap,
      done: this.panMapDone,
      down: this.panMapStart
    }, {
      interval: this.interval,
      documentDrag: this.documentDrag
    })
  },
  panMapStart: function () {
    if (this.kinetic) {
      this.kinetic.begin()
    }
  },
  panMap: function (a) {
    if (this.kinetic) {
      this.kinetic.update(a)
    }
    this.panned = true;
    this.map.pan(this.handler.last.x - a.x, this.handler.last.y - a.y, {
      dragging: true,
      animate: false
    })
  },
  panMapDone: function (c) {
    if (this.panned) {
      var b = null;
      if (this.kinetic) {
        b = this.kinetic.end(c)
      }
      this.map.pan(this.handler.last.x - c.x, this.handler.last.y - c.y, {
        dragging: !!b,
        animate: false
      });
      if (b) {
        var a = this;
        this.kinetic.move(b, function (d, f, e) {
          a.map.pan(d, f, {
            dragging: !e,
            animate: false
          })
        })
      }
      this.panned = false
    }
  },
  CLASS_NAME: 'OpenLayers.Control.DragPan'
}); OpenLayers.Control.ZoomBox = OpenLayers.Class(OpenLayers.Control, {
  type: OpenLayers.Control.TYPE_TOOL,
  out: false,
  keyMask: null,
  alwaysZoom: false,
  zoomOnClick: true,
  draw: function () {
    this.handler = new OpenLayers.Handler.Box(this, {
      done: this.zoomBox
    }, {
      keyMask: this.keyMask
    })
  },
  zoomBox: function (u) {
    if (u instanceof OpenLayers.Bounds) {
      var e,
      n = u.getCenterPixel();
      if (!this.out) {
        var q = this.map.getLonLatFromPixel({
          x: u.left,
          y: u.bottom
        });
        var b = this.map.getLonLatFromPixel({
          x: u.right,
          y: u.top
        });
        e = new OpenLayers.Bounds(q.lon, q.lat, b.lon, b.lat)
      } else {
        var s = u.right - u.left;
        var j = u.bottom - u.top;
        var c = Math.min((this.map.size.h / j), (this.map.size.w / s));
        var g = this.map.getExtent();
        var t = this.map.getLonLatFromPixel(n);
        var h = t.lon - (g.getWidth() / 2) * c;
        var l = t.lon + (g.getWidth() / 2) * c;
        var o = t.lat - (g.getHeight() / 2) * c;
        var p = t.lat + (g.getHeight() / 2) * c;
        e = new OpenLayers.Bounds(h, o, l, p)
      }
      var k = this.map.getZoom(),
      i = this.map.getSize(),
      d = {
        x: i.w / 2,
        y: i.h / 2
      },
      a = this.map.getZoomForExtent(e),
      f = this.map.getResolution(),
      r = this.map.getResolutionForZoom(a);
      if (f == r) {
        this.map.setCenter(this.map.getLonLatFromPixel(n))
      } else {
        var m = {
          x: (f * n.x - r * d.x) / (f - r),
          y: (f * n.y - r * d.y) / (f - r)
        };
        this.map.zoomTo(a, m)
      }
      if (k == this.map.getZoom() && this.alwaysZoom == true) {
        this.map.zoomTo(k + (this.out ? - 1 : 1))
      }
    } else {
      if (this.zoomOnClick) {
        if (!this.out) {
          this.map.zoomTo(this.map.getZoom() + 1, u)
        } else {
          this.map.zoomTo(this.map.getZoom() - 1, u)
        }
      }
    }
  },
  CLASS_NAME: 'OpenLayers.Control.ZoomBox'
}); OpenLayers.Control.Navigation = OpenLayers.Class(OpenLayers.Control, {
  dragPan: null,
  dragPanOptions: null,
  pinchZoom: null,
  pinchZoomOptions: null,
  documentDrag: false,
  zoomBox: null,
  zoomBoxEnabled: true,
  zoomWheelEnabled: true,
  mouseWheelOptions: null,
  handleRightClicks: false,
  zoomBoxKeyMask: OpenLayers.Handler.MOD_SHIFT,
  autoActivate: true,
  initialize: function (a) {
    this.handlers = {
    };
    OpenLayers.Control.prototype.initialize.apply(this, arguments)
  },
  destroy: function () {
    this.deactivate();
    if (this.dragPan) {
      this.dragPan.destroy()
    }
    this.dragPan = null;
    if (this.zoomBox) {
      this.zoomBox.destroy()
    }
    this.zoomBox = null;
    if (this.pinchZoom) {
      this.pinchZoom.destroy()
    }
    this.pinchZoom = null;
    OpenLayers.Control.prototype.destroy.apply(this, arguments)
  },
  activate: function () {
    this.dragPan.activate();
    if (this.zoomWheelEnabled) {
      this.handlers.wheel.activate()
    }
    this.handlers.click.activate();
    if (this.zoomBoxEnabled) {
      this.zoomBox.activate()
    }
    if (this.pinchZoom) {
      this.pinchZoom.activate()
    }
    return OpenLayers.Control.prototype.activate.apply(this, arguments)
  },
  deactivate: function () {
    if (this.pinchZoom) {
      this.pinchZoom.deactivate()
    }
    this.zoomBox.deactivate();
    this.dragPan.deactivate();
    this.handlers.click.deactivate();
    this.handlers.wheel.deactivate();
    return OpenLayers.Control.prototype.deactivate.apply(this, arguments)
  },
  draw: function () {
    if (this.handleRightClicks) {
      this.map.viewPortDiv.oncontextmenu = OpenLayers.Function.False
    }
    var b = {
      click: this.defaultClick,
      dblclick: this.defaultDblClick,
      dblrightclick: this.defaultDblRightClick
    };
    var c = {
      'double': true,
      stopDouble: true
    };
    this.handlers.click = new OpenLayers.Handler.Click(this, b, c);
    this.dragPan = new OpenLayers.Control.DragPan(OpenLayers.Util.extend({
      map: this.map,
      documentDrag: this.documentDrag
    }, this.dragPanOptions));
    this.zoomBox = new OpenLayers.Control.ZoomBox({
      map: this.map,
      keyMask: this.zoomBoxKeyMask
    });
    this.dragPan.draw();
    this.zoomBox.draw();
    var a = this.map.fractionalZoom ? {
    }
     : {
      cumulative: false,
      interval: 50,
      maxDelta: 6
    };
    this.handlers.wheel = new OpenLayers.Handler.MouseWheel(this, {
      up: this.wheelUp,
      down: this.wheelDown
    }, OpenLayers.Util.extend(a, this.mouseWheelOptions));
    if (OpenLayers.Control.PinchZoom) {
      this.pinchZoom = new OpenLayers.Control.PinchZoom(OpenLayers.Util.extend({
        map: this.map
      }, this.pinchZoomOptions))
    }
  },
  defaultClick: function (a) {
    if (a.lastTouches && a.lastTouches.length == 2) {
      this.map.zoomOut()
    }
  },
  defaultDblClick: function (a) {
    this.map.zoomTo(this.map.zoom + 1, a.xy)
  },
  defaultDblRightClick: function (a) {
    this.map.zoomTo(this.map.zoom - 1, a.xy)
  },
  wheelChange: function (a, d) {
    if (!this.map.fractionalZoom) {
      d = Math.round(d)
    }
    var c = this.map.getZoom(),
    b = c + d;
    b = Math.max(b, 0);
    b = Math.min(b, this.map.getNumZoomLevels());
    if (b === c) {
      return
    }
    this.map.zoomTo(b, a.xy)
  },
  wheelUp: function (a, b) {
    this.wheelChange(a, b || 1)
  },
  wheelDown: function (a, b) {
    this.wheelChange(a, b || - 1)
  },
  disableZoomBox: function () {
    this.zoomBoxEnabled = false;
    this.zoomBox.deactivate()
  },
  enableZoomBox: function () {
    this.zoomBoxEnabled = true;
    if (this.active) {
      this.zoomBox.activate()
    }
  },
  disableZoomWheel: function () {
    this.zoomWheelEnabled = false;
    this.handlers.wheel.deactivate()
  },
  enableZoomWheel: function () {
    this.zoomWheelEnabled = true;
    if (this.active) {
      this.handlers.wheel.activate()
    }
  },
  CLASS_NAME: 'OpenLayers.Control.Navigation'
});
OpenLayers.Control.PanZoom = OpenLayers.Class(OpenLayers.Control, {
  slideFactor: 50,
  slideRatio: null,
  buttons: null,
  position: null,
  initialize: function (a) {
    this.position = new OpenLayers.Pixel(OpenLayers.Control.PanZoom.X, OpenLayers.Control.PanZoom.Y);
    OpenLayers.Control.prototype.initialize.apply(this, arguments)
  },
  destroy: function () {
    if (this.map) {
      this.map.events.unregister('buttonclick', this, this.onButtonClick)
    }
    this.removeButtons();
    this.buttons = null;
    this.position = null;
    OpenLayers.Control.prototype.destroy.apply(this, arguments)
  },
  setMap: function (a) {
    OpenLayers.Control.prototype.setMap.apply(this, arguments);
    this.map.events.register('buttonclick', this, this.onButtonClick)
  },
  draw: function (b) {
    OpenLayers.Control.prototype.draw.apply(this, arguments);
    b = this.position;
    this.buttons = [
    ];
    var c = {
      w: 18,
      h: 18
    };
    var a = new OpenLayers.Pixel(b.x + c.w / 2, b.y);
    this._addButton('panup', 'north-mini.png', a, c);
    b.y = a.y + c.h;
    this._addButton('panleft', 'west-mini.png', b, c);
    this._addButton('panright', 'east-mini.png', b.add(c.w, 0), c);
    this._addButton('pandown', 'south-mini.png', a.add(0, c.h * 2), c);
    this._addButton('zoomin', 'zoom-plus-mini.png', a.add(0, c.h * 3 + 5), c);
    this._addButton('zoomworld', 'zoom-world-mini.png', a.add(0, c.h * 4 + 5), c);
    this._addButton('zoomout', 'zoom-minus-mini.png', a.add(0, c.h * 5 + 5), c);
    return this.div
  },
  _addButton: function (f, a, e, d) {
    var c = OpenLayers.Util.getImageLocation(a);
    var b = OpenLayers.Util.createAlphaImageDiv(this.id + '_' + f, e, d, c, 'absolute');
    b.style.cursor = 'pointer';
    this.div.appendChild(b);
    b.action = f;
    b.className = 'olButton';
    this.buttons.push(b);
    return b
  },
  _removeButton: function (a) {
    this.div.removeChild(a);
    OpenLayers.Util.removeItem(this.buttons, a)
  },
  removeButtons: function () {
    for (var a = this.buttons.length - 1; a >= 0; --a) {
      this._removeButton(this.buttons[a])
    }
  },
  onButtonClick: function (a) {
    var b = a.buttonElement;
    switch (b.action) {
      case 'panup':
        this.map.pan(0, - this.getSlideFactor('h'));
        break;
      case 'pandown':
        this.map.pan(0, this.getSlideFactor('h'));
        break;
      case 'panleft':
        this.map.pan( - this.getSlideFactor('w'), 0);
        break;
      case 'panright':
        this.map.pan(this.getSlideFactor('w'), 0);
        break;
      case 'zoomin':
        this.map.zoomIn();
        break;
      case 'zoomout':
        this.map.zoomOut();
        break;
      case 'zoomworld':
        this.map.zoomToMaxExtent();
        break
    }
  },
  getSlideFactor: function (a) {
    return this.slideRatio ? this.map.getSize() [a] * this.slideRatio : this.slideFactor
  },
  CLASS_NAME: 'OpenLayers.Control.PanZoom'
}); OpenLayers.Control.PanZoom.X = 4;
OpenLayers.Control.PanZoom.Y = 4; OpenLayers.Control.ArgParser = OpenLayers.Class(OpenLayers.Control, {
  center: null,
  zoom: null,
  layers: null,
  displayProjection: null,
  getParameters: function (b) {
    b = b || window.location.href;
    var c = OpenLayers.Util.getParameters(b);
    var a = b.indexOf('#');
    if (a > 0) {
      b = '?' + b.substring(a + 1, b.length);
      OpenLayers.Util.extend(c, OpenLayers.Util.getParameters(b))
    }
    return c
  },
  setMap: function (e) {
    OpenLayers.Control.prototype.setMap.apply(this, arguments);
    for (var c = 0, a = this.map.controls.length; c < a; c++) {
      var d = this.map.controls[c];
      if ((d != this) && (d.CLASS_NAME == 'OpenLayers.Control.ArgParser')) {
        if (d.displayProjection != this.displayProjection) {
          this.displayProjection = d.displayProjection
        }
        break
      }
    }
    if (c == this.map.controls.length) {
      var b = this.getParameters();
      if (b.layers) {
        this.layers = b.layers;
        this.map.events.register('addlayer', this, this.configureLayers);
        this.configureLayers()
      }
      if (b.lat && b.lon) {
        this.center = new OpenLayers.LonLat(parseFloat(b.lon), parseFloat(b.lat));
        if (b.zoom) {
          this.zoom = parseFloat(b.zoom)
        }
        this.map.events.register('changebaselayer', this, this.setCenter);
        this.setCenter()
      }
    }
  },
  setCenter: function () {
    if (this.map.baseLayer) {
      this.map.events.unregister('changebaselayer', this, this.setCenter);
      if (this.displayProjection) {
        this.center.transform(this.displayProjection, this.map.getProjectionObject())
      }
      this.map.setCenter(this.center, this.zoom)
    }
  },
  configureLayers: function () {
    if (this.layers.length == this.map.layers.length) {
      this.map.events.unregister('addlayer', this, this.configureLayers);
      for (var d = 0, a = this.layers.length; d < a; d++) {
        var b = this.map.layers[d];
        var e = this.layers.charAt(d);
        if (e == 'B') {
          this.map.setBaseLayer(b)
        } else {
          if ((e == 'T') || (e == 'F')) {
            b.setVisibility(e == 'T')
          }
        }
      }
    }
  },
  CLASS_NAME: 'OpenLayers.Control.ArgParser'
}); OpenLayers.Control.Attribution = OpenLayers.Class(OpenLayers.Control, {
  separator: ', ',
  template: '${layers}',
  destroy: function () {
    this.map.events.un({
      removelayer: this.updateAttribution,
      addlayer: this.updateAttribution,
      changelayer: this.updateAttribution,
      changebaselayer: this.updateAttribution,
      scope: this
    });
    OpenLayers.Control.prototype.destroy.apply(this, arguments)
  },
  draw: function () {
    OpenLayers.Control.prototype.draw.apply(this, arguments);
    this.map.events.on({
      changebaselayer: this.updateAttribution,
      changelayer: this.updateAttribution,
      addlayer: this.updateAttribution,
      removelayer: this.updateAttribution,
      scope: this
    });
    this.updateAttribution();
    return this.div
  },
  updateAttribution: function () {
    var d = [
    ];
    if (this.map && this.map.layers) {
      for (var c = 0, a = this.map.layers.length; c < a; c++) {
        var b = this.map.layers[c];
        if (b.attribution && b.getVisibility()) {
          if (OpenLayers.Util.indexOf(d, b.attribution) === - 1) {
            d.push(b.attribution)
          }
        }
      }
      this.div.innerHTML = OpenLayers.String.format(this.template, {
        layers: d.join(this.separator)
      })
    }
  },
  CLASS_NAME: 'OpenLayers.Control.Attribution'
}); OpenLayers.Control.OverviewMap = OpenLayers.Class(OpenLayers.Control, {
  element: null,
  ovmap: null,
  size: {
    w: 180,
    h: 90
  },
  layers: null,
  minRectSize: 15,
  minRectDisplayClass: 'RectReplacement',
  minRatio: 8,
  maxRatio: 32,
  mapOptions: null,
  autoPan: false,
  handlers: null,
  resolutionFactor: 1,
  maximized: false,
  maximizeTitle: '',
  minimizeTitle: '',
  initialize: function (a) {
    this.layers = [
    ];
    this.handlers = {
    };
    OpenLayers.Control.prototype.initialize.apply(this, [
      a
    ])
  },
  destroy: function () {
    if (!this.mapDiv) {
      return
    }
    if (this.handlers.click) {
      this.handlers.click.destroy()
    }
    if (this.handlers.drag) {
      this.handlers.drag.destroy()
    }
    this.ovmap && this.ovmap.viewPortDiv.removeChild(this.extentRectangle);
    this.extentRectangle = null;
    if (this.rectEvents) {
      this.rectEvents.destroy();
      this.rectEvents = null
    }
    if (this.ovmap) {
      this.ovmap.destroy();
      this.ovmap = null
    }
    this.element.removeChild(this.mapDiv);
    this.mapDiv = null;
    this.div.removeChild(this.element);
    this.element = null;
    if (this.maximizeDiv) {
      this.div.removeChild(this.maximizeDiv);
      this.maximizeDiv = null
    }
    if (this.minimizeDiv) {
      this.div.removeChild(this.minimizeDiv);
      this.minimizeDiv = null
    }
    this.map.events.un({
      buttonclick: this.onButtonClick,
      moveend: this.update,
      changebaselayer: this.baseLayerDraw,
      scope: this
    });
    OpenLayers.Control.prototype.destroy.apply(this, arguments)
  },
  draw: function () {
    OpenLayers.Control.prototype.draw.apply(this, arguments);
    if (this.layers.length === 0) {
      if (this.map.baseLayer) {
        var b = this.map.baseLayer.clone();
        this.layers = [
          b
        ]
      } else {
        this.map.events.register('changebaselayer', this, this.baseLayerDraw);
        return this.div
      }
    }
    this.element = document.createElement('div');
    this.element.className = this.displayClass + 'Element';
    this.element.style.display = 'none';
    this.mapDiv = document.createElement('div');
    this.mapDiv.style.width = this.size.w + 'px';
    this.mapDiv.style.height = this.size.h + 'px';
    this.mapDiv.style.position = 'relative';
    this.mapDiv.style.overflow = 'hidden';
    this.mapDiv.id = OpenLayers.Util.createUniqueID('overviewMap');
    this.extentRectangle = document.createElement('div');
    this.extentRectangle.style.position = 'absolute';
    this.extentRectangle.style.zIndex = 1000;
    this.extentRectangle.className = this.displayClass + 'ExtentRectangle';
    this.element.appendChild(this.mapDiv);
    this.div.appendChild(this.element);
    if (!this.outsideViewport) {
      this.div.className += ' ' + this.displayClass + 'Container';
      var a = OpenLayers.Util.getImageLocation('layer-switcher-maximize.png');
      this.maximizeDiv = OpenLayers.Util.createAlphaImageDiv(this.displayClass + 'MaximizeButton', null, null, a, 'absolute');
      this.maximizeDiv.style.display = 'none';
      this.maximizeDiv.className = this.displayClass + 'MaximizeButton olButton';
      if (this.maximizeTitle) {
        this.maximizeDiv.title = this.maximizeTitle
      }
      this.div.appendChild(this.maximizeDiv);
      var a = OpenLayers.Util.getImageLocation('layer-switcher-minimize.png');
      this.minimizeDiv = OpenLayers.Util.createAlphaImageDiv('OpenLayers_Control_minimizeDiv', null, null, a, 'absolute');
      this.minimizeDiv.style.display = 'none';
      this.minimizeDiv.className = this.displayClass + 'MinimizeButton olButton';
      if (this.minimizeTitle) {
        this.minimizeDiv.title = this.minimizeTitle
      }
      this.div.appendChild(this.minimizeDiv);
      this.minimizeControl()
    } else {
      this.element.style.display = ''
    }
    if (this.map.getExtent()) {
      this.update()
    }
    this.map.events.on({
      buttonclick: this.onButtonClick,
      moveend: this.update,
      scope: this
    });
    if (this.maximized) {
      this.maximizeControl()
    }
    return this.div
  },
  baseLayerDraw: function () {
    this.draw();
    this.map.events.unregister('changebaselayer', this, this.baseLayerDraw)
  },
  rectDrag: function (i) {
    var d = this.handlers.drag.last.x - i.x;
    var b = this.handlers.drag.last.y - i.y;
    if (d != 0 || b != 0) {
      var g = this.rectPxBounds.top;
      var a = this.rectPxBounds.left;
      var e = Math.abs(this.rectPxBounds.getHeight());
      var c = this.rectPxBounds.getWidth();
      var f = Math.max(0, (g - b));
      f = Math.min(f, this.ovmap.size.h - this.hComp - e);
      var h = Math.max(0, (a - d));
      h = Math.min(h, this.ovmap.size.w - this.wComp - c);
      this.setRectPxBounds(new OpenLayers.Bounds(h, f + e, h + c, f))
    }
  },
  mapDivClick: function (i) {
    var b = this.rectPxBounds.getCenterPixel();
    var e = i.xy.x - b.x;
    var d = i.xy.y - b.y;
    var g = this.rectPxBounds.top;
    var c = this.rectPxBounds.left;
    var j = Math.abs(this.rectPxBounds.getHeight());
    var a = this.rectPxBounds.getWidth();
    var f = Math.max(0, (g + d));
    f = Math.min(f, this.ovmap.size.h - j);
    var h = Math.max(0, (c + e));
    h = Math.min(h, this.ovmap.size.w - a);
    this.setRectPxBounds(new OpenLayers.Bounds(h, f + j, h + a, f));
    this.updateMapToRect()
  },
  onButtonClick: function (a) {
    if (a.buttonElement === this.minimizeDiv) {
      this.minimizeControl()
    } else {
      if (a.buttonElement === this.maximizeDiv) {
        this.maximizeControl()
      }
    }
  },
  maximizeControl: function (a) {
    this.element.style.display = '';
    this.showToggle(false);
    if (a != null) {
      OpenLayers.Event.stop(a)
    }
  },
  minimizeControl: function (a) {
    this.element.style.display = 'none';
    this.showToggle(true);
    if (a != null) {
      OpenLayers.Event.stop(a)
    }
  },
  showToggle: function (a) {
    if (this.maximizeDiv) {
      this.maximizeDiv.style.display = a ? '' : 'none'
    }
    if (this.minimizeDiv) {
      this.minimizeDiv.style.display = a ? 'none' : ''
    }
  },
  update: function () {
    if (this.ovmap == null) {
      this.createMap()
    }
    if (this.autoPan || !this.isSuitableOverview()) {
      this.updateOverview()
    }
    this.updateRectToMap()
  },
  isSuitableOverview: function () {
    var b = this.map.getExtent();
    var a = this.map.getMaxExtent();
    var c = new OpenLayers.Bounds(Math.max(b.left, a.left), Math.max(b.bottom, a.bottom), Math.min(b.right, a.right), Math.min(b.top, a.top));
    if (this.ovmap.getProjection() != this.map.getProjection()) {
      c = c.transform(this.map.getProjectionObject(), this.ovmap.getProjectionObject())
    }
    var d = this.ovmap.getResolution() / this.map.getResolution();
    return ((d > this.minRatio) && (d <= this.maxRatio) && (this.ovmap.getExtent().containsBounds(c)))
  },
  updateOverview: function () {
    var c = this.map.getResolution();
    var b = this.ovmap.getResolution();
    var d = b / c;
    if (d > this.maxRatio) {
      b = this.minRatio * c
    } else {
      if (d <= this.minRatio) {
        b = this.maxRatio * c
      }
    }
    var a;
    if (this.ovmap.getProjection() != this.map.getProjection()) {
      a = this.map.center.clone();
      a.transform(this.map.getProjectionObject(), this.ovmap.getProjectionObject())
    } else {
      a = this.map.center
    }
    this.ovmap.setCenter(a, this.ovmap.getZoomForResolution(b * this.resolutionFactor));
    this.updateRectToMap()
  },
  createMap: function () {
    var b = OpenLayers.Util.extend({
      controls: [
      ],
      maxResolution: 'auto',
      fallThrough: false
    }, this.mapOptions);
    this.ovmap = new OpenLayers.Map(this.mapDiv, b);
    this.ovmap.viewPortDiv.appendChild(this.extentRectangle);
    OpenLayers.Event.stopObserving(window, 'unload', this.ovmap.unloadDestroy);
    this.ovmap.addLayers(this.layers);
    this.ovmap.zoomToMaxExtent();
    this.wComp = parseInt(OpenLayers.Element.getStyle(this.extentRectangle, 'border-left-width')) + parseInt(OpenLayers.Element.getStyle(this.extentRectangle, 'border-right-width'));
    this.wComp = (this.wComp) ? this.wComp : 2;
    this.hComp = parseInt(OpenLayers.Element.getStyle(this.extentRectangle, 'border-top-width')) + parseInt(OpenLayers.Element.getStyle(this.extentRectangle, 'border-bottom-width'));
    this.hComp = (this.hComp) ? this.hComp : 2;
    this.handlers.drag = new OpenLayers.Handler.Drag(this, {
      move: this.rectDrag,
      done: this.updateMapToRect
    }, {
      map: this.ovmap
    });
    this.handlers.click = new OpenLayers.Handler.Click(this, {
      click: this.mapDivClick
    }, {
      single: true,
      'double': false,
      stopSingle: true,
      stopDouble: true,
      pixelTolerance: 1,
      map: this.ovmap
    });
    this.handlers.click.activate();
    this.rectEvents = new OpenLayers.Events(this, this.extentRectangle, null, true);
    this.rectEvents.register('mouseover', this, function (d) {
      if (!this.handlers.drag.active && !this.map.dragging) {
        this.handlers.drag.activate()
      }
    });
    this.rectEvents.register('mouseout', this, function (d) {
      if (!this.handlers.drag.dragging) {
        this.handlers.drag.deactivate()
      }
    });
    if (this.ovmap.getProjection() != this.map.getProjection()) {
      var c = this.map.getProjectionObject().getUnits() || this.map.units || this.map.baseLayer.units;
      var a = this.ovmap.getProjectionObject().getUnits() || this.ovmap.units || this.ovmap.baseLayer.units;
      this.resolutionFactor = c && a ? OpenLayers.INCHES_PER_UNIT[c] / OpenLayers.INCHES_PER_UNIT[a] : 1
    }
  },
  updateRectToMap: function () {
    var b;
    if (this.ovmap.getProjection() != this.map.getProjection()) {
      b = this.map.getExtent().transform(this.map.getProjectionObject(), this.ovmap.getProjectionObject())
    } else {
      b = this.map.getExtent()
    }
    var a = this.getRectBoundsFromMapBounds(b);
    if (a) {
      this.setRectPxBounds(a)
    }
  },
  updateMapToRect: function () {
    var a = this.getMapBoundsFromRectBounds(this.rectPxBounds);
    if (this.ovmap.getProjection() != this.map.getProjection()) {
      a = a.transform(this.ovmap.getProjectionObject(), this.map.getProjectionObject())
    }
    this.map.panTo(a.getCenterLonLat())
  },
  setRectPxBounds: function (d) {
    var g = Math.max(d.top, 0);
    var e = Math.max(d.left, 0);
    var b = Math.min(d.top + Math.abs(d.getHeight()), this.ovmap.size.h - this.hComp);
    var h = Math.min(d.left + d.getWidth(), this.ovmap.size.w - this.wComp);
    var c = Math.max(h - e, 0);
    var i = Math.max(b - g, 0);
    if (c < this.minRectSize || i < this.minRectSize) {
      this.extentRectangle.className = this.displayClass + this.minRectDisplayClass;
      var f = e + (c / 2) - (this.minRectSize / 2);
      var a = g + (i / 2) - (this.minRectSize / 2);
      this.extentRectangle.style.top = Math.round(a) + 'px';
      this.extentRectangle.style.left = Math.round(f) + 'px';
      this.extentRectangle.style.height = this.minRectSize + 'px';
      this.extentRectangle.style.width = this.minRectSize + 'px'
    } else {
      this.extentRectangle.className = this.displayClass + 'ExtentRectangle';
      this.extentRectangle.style.top = Math.round(g) + 'px';
      this.extentRectangle.style.left = Math.round(e) + 'px';
      this.extentRectangle.style.height = Math.round(i) + 'px';
      this.extentRectangle.style.width = Math.round(c) + 'px'
    }
    this.rectPxBounds = new OpenLayers.Bounds(Math.round(e), Math.round(b), Math.round(h), Math.round(g))
  },
  getRectBoundsFromMapBounds: function (c) {
    var b = this.getOverviewPxFromLonLat({
      lon: c.left,
      lat: c.bottom
    });
    var a = this.getOverviewPxFromLonLat({
      lon: c.right,
      lat: c.top
    });
    var d = null;
    if (b && a) {
      d = new OpenLayers.Bounds(b.x, b.y, a.x, a.y)
    }
    return d
  },
  getMapBoundsFromRectBounds: function (b) {
    var a = this.getLonLatFromOverviewPx({
      x: b.left,
      y: b.bottom
    });
    var c = this.getLonLatFromOverviewPx({
      x: b.right,
      y: b.top
    });
    return new OpenLayers.Bounds(a.lon, a.lat, c.lon, c.lat)
  },
  getLonLatFromOverviewPx: function (f) {
    var e = this.ovmap.size;
    var d = this.ovmap.getResolution();
    var b = this.ovmap.getExtent().getCenterLonLat();
    var c = f.x - (e.w / 2);
    var a = f.y - (e.h / 2);
    return {
      lon: b.lon + c * d,
      lat: b.lat - a * d
    }
  },
  getOverviewPxFromLonLat: function (c) {
    var a = this.ovmap.getResolution();
    var b = this.ovmap.getExtent();
    if (b) {
      return {
        x: Math.round(1 / a * (c.lon - b.left)),
        y: Math.round(1 / a * (b.top - c.lat))
      }
    }
  },
  CLASS_NAME: 'OpenLayers.Control.OverviewMap'
}); OpenLayers.Animation = (function (f) {
  var g = OpenLayers.Util.vendorPrefix.js(f, 'requestAnimationFrame');
  var c = !!(g);
  var e = (function () {
    var i = f[g] || function (k, j) {
      f.setTimeout(k, 16)
    };
    return function (k, j) {
      i.apply(f, [
        k,
        j
      ])
    }
  }) ();
  var b = 0;
  var a = {
  };
  function h(m, j, i) {
    j = j > 0 ? j : Number.POSITIVE_INFINITY;
    var l = ++b;
    var k = + new Date;
    a[l] = function () {
      if (a[l] && + new Date - k <= j) {
        m();
        if (a[l]) {
          e(a[l], i)
        }
      } else {
        delete a[l]
      }
    };
    e(a[l], i);
    return l
  }
  function d(i) {
    delete a[i]
  }
  return {
    isNative: c,
    requestFrame: e,
    start: h,
    stop: d
  }
}) (window); OpenLayers.Tween = OpenLayers.Class({
  easing: null,
  begin: null,
  finish: null,
  duration: null,
  callbacks: null,
  time: null,
  minFrameRate: null,
  startTime: null,
  animationId: null,
  playing: false,
  initialize: function (a) {
    this.easing = (a) ? a : OpenLayers.Easing.Expo.easeOut
  },
  start: function (c, b, d, a) {
    this.playing = true;
    this.begin = c;
    this.finish = b;
    this.duration = d;
    this.callbacks = a.callbacks;
    this.minFrameRate = a.minFrameRate || 30;
    this.time = 0;
    this.startTime = new Date().getTime();
    OpenLayers.Animation.stop(this.animationId);
    this.animationId = null;
    if (this.callbacks && this.callbacks.start) {
      this.callbacks.start.call(this, this.begin)
    }
    this.animationId = OpenLayers.Animation.start(OpenLayers.Function.bind(this.play, this))
  },
  stop: function () {
    if (!this.playing) {
      return
    }
    if (this.callbacks && this.callbacks.done) {
      this.callbacks.done.call(this, this.finish)
    }
    OpenLayers.Animation.stop(this.animationId);
    this.animationId = null;
    this.playing = false
  },
  play: function () {
    var g = {
    };
    for (var d in this.begin) {
      var a = this.begin[d];
      var e = this.finish[d];
      if (a == null || e == null || isNaN(a) || isNaN(e)) {
        throw new TypeError('invalid value for Tween')
      }
      var h = e - a;
      g[d] = this.easing.apply(this, [
        this.time,
        a,
        h,
        this.duration
      ])
    }
    this.time++;
    if (this.callbacks && this.callbacks.eachStep) {
      if ((new Date().getTime() - this.startTime) / this.time <= 1000 / this.minFrameRate) {
        this.callbacks.eachStep.call(this, g)
      }
    }
    if (this.time > this.duration) {
      this.stop()
    }
  },
  CLASS_NAME: 'OpenLayers.Tween'
}); OpenLayers.Easing = {
  CLASS_NAME: 'OpenLayers.Easing'
}; OpenLayers.Easing.Linear = {
  easeIn: function (e, a, g, f) {
    return g * e / f + a
  },
  easeOut: function (e, a, g, f) {
    return g * e / f + a
  },
  easeInOut: function (e, a, g, f) {
    return g * e / f + a
  },
  CLASS_NAME: 'OpenLayers.Easing.Linear'
};
OpenLayers.Easing.Expo = {
  easeIn: function (e, a, g, f) {
    return (e == 0) ? a : g * Math.pow(2, 10 * (e / f - 1)) + a
  },
  easeOut: function (e, a, g, f) {
    return (e == f) ? a + g : g * ( - Math.pow(2, - 10 * e / f) + 1) + a
  },
  easeInOut: function (e, a, g, f) {
    if (e == 0) {
      return a
    }
    if (e == f) {
      return a + g
    }
    if ((e /= f / 2) < 1) {
      return g / 2 * Math.pow(2, 10 * (e - 1)) + a
    }
    return g / 2 * ( - Math.pow(2, - 10 * --e) + 2) + a
  },
  CLASS_NAME: 'OpenLayers.Easing.Expo'
}; OpenLayers.Easing.Quad = {
  easeIn: function (e, a, g, f) {
    return g * (e /= f) * e + a
  },
  easeOut: function (e, a, g, f) {
    return - g * (e /= f) * (e - 2) + a
  },
  easeInOut: function (e, a, g, f) {
    if ((e /= f / 2) < 1) {
      return g / 2 * e * e + a
    }
    return - g / 2 * ((--e) * (e - 2) - 1) + a
  },
  CLASS_NAME: 'OpenLayers.Easing.Quad'
}; OpenLayers.Projection = OpenLayers.Class({
  proj: null,
  projCode: null,
  titleRegEx: /\+title=[^\+]*/,
  initialize: function (b, a) {
    OpenLayers.Util.extend(this, a);
    this.projCode = b;
    if (typeof Proj4js == 'object') {
      this.proj = new Proj4js.Proj(b)
    }
  },
  getCode: function () {
    return this.proj ? this.proj.srsCode : this.projCode
  },
  getUnits: function () {
    return this.proj ? this.proj.units : null
  },
  toString: function () {
    return this.getCode()
  },
  equals: function (a) {
    var e = a,
    b = false;
    if (e) {
      if (!(e instanceof OpenLayers.Projection)) {
        e = new OpenLayers.Projection(e)
      }
      if ((typeof Proj4js == 'object') && this.proj.defData && e.proj.defData) {
        b = this.proj.defData.replace(this.titleRegEx, '') == e.proj.defData.replace(this.titleRegEx, '')
      } else {
        if (e.getCode) {
          var c = this.getCode(),
          d = e.getCode();
          b = c == d || !!OpenLayers.Projection.transforms[c] && OpenLayers.Projection.transforms[c][d] === OpenLayers.Projection.nullTransform
        }
      }
    }
    return b
  },
  destroy: function () {
    delete this.proj;
    delete this.projCode
  },
  CLASS_NAME: 'OpenLayers.Projection'
}); OpenLayers.Projection.transforms = {
}; OpenLayers.Projection.defaults = {
  'EPSG:4326': {
    units: 'degrees',
    maxExtent: [
      - 180,
      - 90,
      180,
      90
    ],
    yx: true
  },
  'CRS:84': {
    units: 'degrees',
    maxExtent: [
      - 180,
      - 90,
      180,
      90
    ]
  },
  'EPSG:900913': {
    units: 'm',
    maxExtent: [
      - 20037508.34,
      - 20037508.34,
      20037508.34,
      20037508.34
    ]
  }
};
OpenLayers.Projection.addTransform = function (d, c, b) {
  if (b === OpenLayers.Projection.nullTransform) {
    var a = OpenLayers.Projection.defaults[d];
    if (a && !OpenLayers.Projection.defaults[c]) {
      OpenLayers.Projection.defaults[c] = a
    }
  }
  if (!OpenLayers.Projection.transforms[d]) {
    OpenLayers.Projection.transforms[d] = {
    }
  }
  OpenLayers.Projection.transforms[d][c] = b
}; OpenLayers.Projection.transform = function (a, e, b) {
  if (e && b) {
    if (!(e instanceof OpenLayers.Projection)) {
      e = new OpenLayers.Projection(e)
    }
    if (!(b instanceof OpenLayers.Projection)) {
      b = new OpenLayers.Projection(b)
    }
    if (e.proj && b.proj) {
      a = Proj4js.transform(e.proj, b.proj, a)
    } else {
      var d = e.getCode();
      var f = b.getCode();
      var c = OpenLayers.Projection.transforms;
      if (c[d] && c[d][f]) {
        c[d][f](a)
      }
    }
  }
  return a
}; OpenLayers.Projection.nullTransform = function (a) {
  return a
}; (function () {
  var e = 20037508.34;
  function g(h) {
    h.x = 180 * h.x / e;
    h.y = 180 / Math.PI * (2 * Math.atan(Math.exp((h.y / e) * Math.PI)) - Math.PI / 2);
    return h
  }
  function b(h) {
    h.x = h.x * e / 180;
    var i = Math.log(Math.tan((90 + h.y) * Math.PI / 360)) / Math.PI * e;
    h.y = Math.max( - 20037508.34, Math.min(i, 20037508.34));
    return h
  }
  function f(k, h) {
    var r = OpenLayers.Projection.addTransform;
    var q = OpenLayers.Projection.nullTransform;
    var n,
    o,
    l,
    p,
    m;
    for (n = 0, o = h.length; n < o; ++n) {
      l = h[n];
      r(k, l, b);
      r(l, k, g);
      for (m = n + 1; m < o; ++m) {
        p = h[m];
        r(l, p, q);
        r(p, l, q)
      }
    }
  }
  var a = [
    'EPSG:900913',
    'EPSG:3857',
    'EPSG:102113',
    'EPSG:102100'
  ],
  d = [
    'CRS:84',
    'urn:ogc:def:crs:EPSG:6.6:4326',
    'EPSG:4326'
  ],
  c;
  for (c = a.length - 1; c >= 0; --c) {
    f(a[c], d)
  }
  for (c = d.length - 1; c >= 0; --c) {
    f(d[c], a)
  }
}) (); OpenLayers.Map = OpenLayers.Class({
  Z_INDEX_BASE: {
    BaseLayer: 100,
    Overlay: 325,
    Feature: 725,
    Popup: 750,
    Control: 1000
  },
  id: null,
  fractionalZoom: false,
  events: null,
  allOverlays: false,
  div: null,
  dragging: false,
  size: null,
  viewPortDiv: null,
  layerContainerOrigin: null,
  layerContainerDiv: null,
  layers: null,
  controls: null,
  popups: null,
  baseLayer: null,
  center: null,
  resolution: null,
  zoom: 0,
  panRatio: 1.5,
  options: null,
  tileSize: null,
  projection: 'EPSG:4326',
  units: null,
  resolutions: null,
  maxResolution: null,
  minResolution: null,
  maxScale: null,
  minScale: null,
  maxExtent: null,
  minExtent: null,
  restrictedExtent: null,
  numZoomLevels: 16,
  theme: null,
  displayProjection: null,
  fallThrough: false,
  autoUpdateSize: true,
  eventListeners: null,
  panTween: null,
  panMethod: OpenLayers.Easing.Expo.easeOut,
  panDuration: 50,
  zoomTween: null,
  zoomMethod: OpenLayers.Easing.Quad.easeOut,
  zoomDuration: 20,
  paddingForPopups: null,
  layerContainerOriginPx: null,
  minPx: null,
  maxPx: null,
  initialize: function (b, j) {
    if (arguments.length === 1 && typeof b === 'object') {
      j = b;
      b = j && j.div
    }
    this.tileSize = new OpenLayers.Size(OpenLayers.Map.TILE_WIDTH, OpenLayers.Map.TILE_HEIGHT);
    this.paddingForPopups = new OpenLayers.Bounds(15, 15, 15, 15);
    this.theme = OpenLayers._getScriptLocation() + 'theme/default/style.css';
    this.options = OpenLayers.Util.extend({
    }, j);
    OpenLayers.Util.extend(this, j);
    var a = this.projection instanceof OpenLayers.Projection ? this.projection.projCode : this.projection;
    OpenLayers.Util.applyDefaults(this, OpenLayers.Projection.defaults[a]);
    if (this.maxExtent && !(this.maxExtent instanceof OpenLayers.Bounds)) {
      this.maxExtent = new OpenLayers.Bounds(this.maxExtent)
    }
    if (this.minExtent && !(this.minExtent instanceof OpenLayers.Bounds)) {
      this.minExtent = new OpenLayers.Bounds(this.minExtent)
    }
    if (this.restrictedExtent && !(this.restrictedExtent instanceof OpenLayers.Bounds)) {
      this.restrictedExtent = new OpenLayers.Bounds(this.restrictedExtent)
    }
    if (this.center && !(this.center instanceof OpenLayers.LonLat)) {
      this.center = new OpenLayers.LonLat(this.center)
    }
    this.layers = [
    ];
    this.id = OpenLayers.Util.createUniqueID('OpenLayers.Map_');
    this.div = OpenLayers.Util.getElement(b);
    if (!this.div) {
      this.div = document.createElement('div');
      this.div.style.height = '1px';
      this.div.style.width = '1px'
    }
    OpenLayers.Element.addClass(this.div, 'olMap');
    var d = this.id + '_OpenLayers_ViewPort';
    this.viewPortDiv = OpenLayers.Util.createDiv(d, null, null, null, 'relative', null, 'hidden');
    this.viewPortDiv.style.width = '100%';
    this.viewPortDiv.style.height = '100%';
    this.viewPortDiv.className = 'olMapViewport';
    this.div.appendChild(this.viewPortDiv);
    this.events = new OpenLayers.Events(this, this.viewPortDiv, null, this.fallThrough, {
      includeXY: true
    });
    if (OpenLayers.TileManager && this.tileManager !== null) {
      if (!(this.tileManager instanceof OpenLayers.TileManager)) {
        this.tileManager = new OpenLayers.TileManager(this.tileManager)
      }
      this.tileManager.addMap(this)
    }
    d = this.id + '_OpenLayers_Container';
    this.layerContainerDiv = OpenLayers.Util.createDiv(d);
    this.layerContainerDiv.style.zIndex = this.Z_INDEX_BASE.Popup - 1;
    this.layerContainerOriginPx = {
      x: 0,
      y: 0
    };
    this.applyTransform();
    this.viewPortDiv.appendChild(this.layerContainerDiv);
    this.updateSize();
    if (this.eventListeners instanceof Object) {
      this.events.on(this.eventListeners)
    }
    if (this.autoUpdateSize === true) {
      this.updateSizeDestroy = OpenLayers.Function.bind(this.updateSize, this);
      OpenLayers.Event.observe(window, 'resize', this.updateSizeDestroy)
    }
    if (this.theme) {
      var e = true;
      var c = document.getElementsByTagName('link');
      for (var f = 0, g = c.length; f < g; ++f) {
        if (OpenLayers.Util.isEquivalentUrl(c.item(f).href, this.theme)) {
          e = false;
          break
        }
      }
      if (e) {
        var h = document.createElement('link');
        h.setAttribute('rel', 'stylesheet');
        h.setAttribute('type', 'text/css');
        h.setAttribute('href', this.theme);
        document.getElementsByTagName('head') [0].appendChild(h)
      }
    }
    if (this.controls == null) {
      this.controls = [
      ];
      if (OpenLayers.Control != null) {
        if (OpenLayers.Control.Navigation) {
          this.controls.push(new OpenLayers.Control.Navigation())
        } else {
          if (OpenLayers.Control.TouchNavigation) {
            this.controls.push(new OpenLayers.Control.TouchNavigation())
          }
        }
        if (OpenLayers.Control.Zoom) {
          this.controls.push(new OpenLayers.Control.Zoom())
        } else {
          if (OpenLayers.Control.PanZoom) {
            this.controls.push(new OpenLayers.Control.PanZoom())
          }
        }
        if (OpenLayers.Control.ArgParser) {
          this.controls.push(new OpenLayers.Control.ArgParser())
        }
        if (OpenLayers.Control.Attribution) {
          this.controls.push(new OpenLayers.Control.Attribution())
        }
      }
    }
    for (var f = 0, g = this.controls.length;
    f < g; f++) {
      this.addControlToMap(this.controls[f])
    }
    this.popups = [
    ];
    this.unloadDestroy = OpenLayers.Function.bind(this.destroy, this);
    OpenLayers.Event.observe(window, 'unload', this.unloadDestroy);
    if (j && j.layers) {
      delete this.center;
      delete this.zoom;
      this.addLayers(j.layers);
      if (j.center && !this.getCenter()) {
        this.setCenter(j.center, j.zoom)
      }
    }
    if (this.panMethod) {
      this.panTween = new OpenLayers.Tween(this.panMethod)
    }
    if (this.zoomMethod && this.applyTransform.transform) {
      this.zoomTween = new OpenLayers.Tween(this.zoomMethod)
    }
  },
  getViewport: function () {
    return this.viewPortDiv
  },
  render: function (a) {
    this.div = OpenLayers.Util.getElement(a);
    OpenLayers.Element.addClass(this.div, 'olMap');
    this.viewPortDiv.parentNode.removeChild(this.viewPortDiv);
    this.div.appendChild(this.viewPortDiv);
    this.updateSize()
  },
  unloadDestroy: null,
  updateSizeDestroy: null,
  destroy: function () {
    if (!this.unloadDestroy) {
      return false
    }
    if (this.panTween) {
      this.panTween.stop();
      this.panTween = null
    }
    if (this.zoomTween) {
      this.zoomTween.stop();
      this.zoomTween = null
    }
    OpenLayers.Event.stopObserving(window, 'unload', this.unloadDestroy);
    this.unloadDestroy = null;
    if (this.updateSizeDestroy) {
      OpenLayers.Event.stopObserving(window, 'resize', this.updateSizeDestroy)
    }
    this.paddingForPopups = null;
    if (this.controls != null) {
      for (var a = this.controls.length - 1; a >= 0; --a) {
        this.controls[a].destroy()
      }
      this.controls = null
    }
    if (this.layers != null) {
      for (var a = this.layers.length - 1; a >= 0; --a) {
        this.layers[a].destroy(false)
      }
      this.layers = null
    }
    if (this.viewPortDiv && this.viewPortDiv.parentNode) {
      this.viewPortDiv.parentNode.removeChild(this.viewPortDiv)
    }
    this.viewPortDiv = null;
    if (this.tileManager) {
      this.tileManager.removeMap(this);
      this.tileManager = null
    }
    if (this.eventListeners) {
      this.events.un(this.eventListeners);
      this.eventListeners = null
    }
    this.events.destroy();
    this.events = null;
    this.options = null
  },
  setOptions: function (a) {
    var b = this.minPx && a.restrictedExtent != this.restrictedExtent;
    OpenLayers.Util.extend(this, a);
    b && this.moveTo(this.getCachedCenter(), this.zoom, {
      forceZoomChange: true
    })
  },
  getTileSize: function () {
    return this.tileSize
  },
  getBy: function (e, c, a) {
    var d = (typeof a.test == 'function');
    var b = OpenLayers.Array.filter(this[e], function (f) {
      return f[c] == a || (d && a.test(f[c]))
    });
    return b
  },
  getLayersBy: function (b, a) {
    return this.getBy('layers', b, a)
  },
  getLayersByName: function (a) {
    return this.getLayersBy('name', a)
  },
  getLayersByClass: function (a) {
    return this.getLayersBy('CLASS_NAME', a)
  },
  getControlsBy: function (b, a) {
    return this.getBy('controls', b, a)
  },
  getControlsByClass: function (a) {
    return this.getControlsBy('CLASS_NAME', a)
  },
  getLayer: function (e) {
    var b = null;
    for (var d = 0, a = this.layers.length; d < a; d++) {
      var c = this.layers[d];
      if (c.id == e) {
        b = c;
        break
      }
    }
    return b
  },
  setLayerZIndex: function (b, a) {
    b.setZIndex(this.Z_INDEX_BASE[b.isBaseLayer ? 'BaseLayer' : 'Overlay'] + a * 5)
  },
  resetLayersZIndex: function () {
    for (var c = 0, a = this.layers.length; c < a; c++) {
      var b = this.layers[c];
      this.setLayerZIndex(b, c)
    }
  },
  addLayer: function (c) {
    for (var b = 0, a = this.layers.length;
    b < a; b++) {
      if (this.layers[b] == c) {
        return false
      }
    }
    if (this.events.triggerEvent('preaddlayer', {
      layer: c
    }) === false) {
      return false
    }
    if (this.allOverlays) {
      c.isBaseLayer = false
    }
    c.div.className = 'olLayerDiv';
    c.div.style.overflow = '';
    this.setLayerZIndex(c, this.layers.length);
    if (c.isFixed) {
      this.viewPortDiv.appendChild(c.div)
    } else {
      this.layerContainerDiv.appendChild(c.div)
    }
    this.layers.push(c);
    c.setMap(this);
    if (c.isBaseLayer || (this.allOverlays && !this.baseLayer)) {
      if (this.baseLayer == null) {
        this.setBaseLayer(c)
      } else {
        c.setVisibility(false)
      }
    } else {
      c.redraw()
    }
    this.events.triggerEvent('addlayer', {
      layer: c
    });
    c.events.triggerEvent('added', {
      map: this,
      layer: c
    });
    c.afterAdd();
    return true
  },
  addLayers: function (c) {
    for (var b = 0, a = c.length; b < a; b++) {
      this.addLayer(c[b])
    }
  },
  removeLayer: function (c, e) {
    if (this.events.triggerEvent('preremovelayer', {
      layer: c
    }) === false) {
      return
    }
    if (e == null) {
      e = true
    }
    if (c.isFixed) {
      this.viewPortDiv.removeChild(c.div)
    } else {
      this.layerContainerDiv.removeChild(c.div)
    }
    OpenLayers.Util.removeItem(this.layers, c);
    c.removeMap(this);
    c.map = null;
    if (this.baseLayer == c) {
      this.baseLayer = null;
      if (e) {
        for (var b = 0, a = this.layers.length; b < a; b++) {
          var d = this.layers[b];
          if (d.isBaseLayer || this.allOverlays) {
            this.setBaseLayer(d);
            break
          }
        }
      }
    }
    this.resetLayersZIndex();
    this.events.triggerEvent('removelayer', {
      layer: c
    });
    c.events.triggerEvent('removed', {
      map: this,
      layer: c
    })
  },
  getNumLayers: function () {
    return this.layers.length
  },
  getLayerIndex: function (a) {
    return OpenLayers.Util.indexOf(this.layers, a)
  },
  setLayerIndex: function (d, b) {
    var e = this.getLayerIndex(d);
    if (b < 0) {
      b = 0
    } else {
      if (b > this.layers.length) {
        b = this.layers.length
      }
    }
    if (e != b) {
      this.layers.splice(e, 1);
      this.layers.splice(b, 0, d);
      for (var c = 0, a = this.layers.length; c < a; c++) {
        this.setLayerZIndex(this.layers[c], c)
      }
      this.events.triggerEvent('changelayer', {
        layer: d,
        property: 'order'
      });
      if (this.allOverlays) {
        if (b === 0) {
          this.setBaseLayer(d)
        } else {
          if (this.baseLayer !== this.layers[0]) {
            this.setBaseLayer(this.layers[0])
          }
        }
      }
    }
  },
  raiseLayer: function (b, c) {
    var a = this.getLayerIndex(b) + c;
    this.setLayerIndex(b, a)
  },
  setBaseLayer: function (c) {
    if (c != this.baseLayer) {
      if (OpenLayers.Util.indexOf(this.layers, c) != - 1) {
        var a = this.getCachedCenter();
        var d = OpenLayers.Util.getResolutionFromScale(this.getScale(), c.units);
        if (this.baseLayer != null && !this.allOverlays) {
          this.baseLayer.setVisibility(false)
        }
        this.baseLayer = c;
        if (!this.allOverlays || this.baseLayer.visibility) {
          this.baseLayer.setVisibility(true);
          if (this.baseLayer.inRange === false) {
            this.baseLayer.redraw()
          }
        }
        if (a != null) {
          var b = this.getZoomForResolution(d || this.resolution, true);
          this.setCenter(a, b, false, true)
        }
        this.events.triggerEvent('changebaselayer', {
          layer: this.baseLayer
        })
      }
    }
  },
  addControl: function (b, a) {
    this.controls.push(b);
    this.addControlToMap(b, a)
  },
  addControls: function (b, g) {
    var e = (arguments.length === 1) ? [
    ] : g;
    for (var d = 0, a = b.length; d < a; d++) {
      var f = b[d];
      var c = (e[d]) ? e[d] : null;
      this.addControl(f, c)
    }
  },
  addControlToMap: function (b, a) {
    b.outsideViewport = (b.div != null);
    if (this.displayProjection && !b.displayProjection) {
      b.displayProjection = this.displayProjection
    }
    b.setMap(this);
    var c = b.draw(a);
    if (c) {
      if (!b.outsideViewport) {
        c.style.zIndex = this.Z_INDEX_BASE.Control + this.controls.length;
        this.viewPortDiv.appendChild(c)
      }
    }
    if (b.autoActivate) {
      b.activate()
    }
  },
  getControl: function (e) {
    var b = null;
    for (var c = 0, a = this.controls.length; c < a; c++) {
      var d = this.controls[c];
      if (d.id == e) {
        b = d;
        break
      }
    }
    return b
  },
  removeControl: function (a) {
    if ((a) && (a == this.getControl(a.id))) {
      if (a.div && (a.div.parentNode == this.viewPortDiv)) {
        this.viewPortDiv.removeChild(a.div)
      }
      OpenLayers.Util.removeItem(this.controls, a)
    }
  },
  addPopup: function (a, d) {
    if (d) {
      for (var b = this.popups.length - 1; b >= 0; --b) {
        this.removePopup(this.popups[b])
      }
    }
    a.map = this;
    this.popups.push(a);
    var c = a.draw();
    if (c) {
      c.style.zIndex = this.Z_INDEX_BASE.Popup + this.popups.length;
      this.layerContainerDiv.appendChild(c)
    }
  },
  removePopup: function (a) {
    OpenLayers.Util.removeItem(this.popups, a);
    if (a.div) {
      try {
        this.layerContainerDiv.removeChild(a.div)
      } catch (b) {
      }
    }
    a.map = null
  },
  getSize: function () {
    var a = null;
    if (this.size != null) {
      a = this.size.clone()
    }
    return a
  },
  updateSize: function () {
    var c = this.getCurrentSize();
    if (c && !isNaN(c.h) && !isNaN(c.w)) {
      this.events.clearMouseCache();
      var f = this.getSize();
      if (f == null) {
        this.size = f = c
      }
      if (!c.equals(f)) {
        this.size = c;
        for (var d = 0, b = this.layers.length; d < b; d++) {
          this.layers[d].onMapResize()
        }
        var a = this.getCachedCenter();
        if (this.baseLayer != null && a != null) {
          var e = this.getZoom();
          this.zoom = null;
          this.setCenter(a, e)
        }
      }
    }
    this.events.triggerEvent('updatesize')
  },
  getCurrentSize: function () {
    var a = new OpenLayers.Size(this.div.clientWidth, this.div.clientHeight);
    if (a.w == 0 && a.h == 0 || isNaN(a.w) && isNaN(a.h)) {
      a.w = this.div.offsetWidth;
      a.h = this.div.offsetHeight
    }
    if (a.w == 0 && a.h == 0 || isNaN(a.w) && isNaN(a.h)) {
      a.w = parseInt(this.div.style.width);
      a.h = parseInt(this.div.style.height)
    }
    return a
  },
  calculateBounds: function (a, b) {
    var c = null;
    if (a == null) {
      a = this.getCachedCenter()
    }
    if (b == null) {
      b = this.getResolution()
    }
    if ((a != null) && (b != null)) {
      var d = (this.size.w * b) / 2;
      var e = (this.size.h * b) / 2;
      c = new OpenLayers.Bounds(a.lon - d, a.lat - e, a.lon + d, a.lat + e)
    }
    return c
  },
  getCenter: function () {
    var a = null;
    var b = this.getCachedCenter();
    if (b) {
      a = b.clone()
    }
    return a
  },
  getCachedCenter: function () {
    if (!this.center && this.size) {
      this.center = this.getLonLatFromViewPortPx({
        x: this.size.w / 2,
        y: this.size.h / 2
      })
    }
    return this.center
  },
  getZoom: function () {
    return this.zoom
  },
  pan: function (d, c, e) {
    e = OpenLayers.Util.applyDefaults(e, {
      animate: true,
      dragging: false
    });
    if (e.dragging) {
      if (d != 0 || c != 0) {
        this.moveByPx(d, c)
      }
    } else {
      var f = this.getViewPortPxFromLonLat(this.getCachedCenter());
      var b = f.add(d, c);
      if (this.dragging || !b.equals(f)) {
        var a = this.getLonLatFromViewPortPx(b);
        if (e.animate) {
          this.panTo(a)
        } else {
          this.moveTo(a);
          if (this.dragging) {
            this.dragging = false;
            this.events.triggerEvent('moveend')
          }
        }
      }
    }
  },
  panTo: function (d) {
    if (this.panTween && this.getExtent().scale(this.panRatio).containsLonLat(d)) {
      var a = this.getCachedCenter();
      if (d.equals(a)) {
        return
      }
      var f = this.getPixelFromLonLat(a);
      var e = this.getPixelFromLonLat(d);
      var b = {
        x: e.x - f.x,
        y: e.y - f.y
      };
      var c = {
        x: 0,
        y: 0
      };
      this.panTween.start({
        x: 0,
        y: 0
      }, b, this.panDuration, {
        callbacks: {
          eachStep: OpenLayers.Function.bind(function (h) {
            var g = h.x - c.x,
            i = h.y - c.y;
            this.moveByPx(g, i);
            c.x = Math.round(h.x);
            c.y = Math.round(h.y)
          }, this),
          done: OpenLayers.Function.bind(function (g) {
            this.moveTo(d);
            this.dragging = false;
            this.events.triggerEvent('moveend')
          }, this)
        }
      })
    } else {
      this.setCenter(d)
    }
  },
  setCenter: function (c, a, b, d) {
    if (this.panTween) {
      this.panTween.stop()
    }
    if (this.zoomTween) {
      this.zoomTween.stop()
    }
    this.moveTo(c, a, {
      dragging: b,
      forceZoomChange: d
    })
  },
  moveByPx: function (m, l) {
    var f = this.size.w / 2;
    var a = this.size.h / 2;
    var j = f + m;
    var h = a + l;
    var b = this.baseLayer.wrapDateLine;
    var k = 0;
    var g = 0;
    if (this.restrictedExtent) {
      k = f;
      g = a;
      b = false
    }
    m = b || j <= this.maxPx.x - k && j >= this.minPx.x + k ? Math.round(m)  : 0;
    l = h <= this.maxPx.y - g && h >= this.minPx.y + g ? Math.round(l)  : 0;
    if (m || l) {
      if (!this.dragging) {
        this.dragging = true;
        this.events.triggerEvent('movestart')
      }
      this.center = null;
      if (m) {
        this.layerContainerOriginPx.x -= m;
        this.minPx.x -= m;
        this.maxPx.x -= m
      }
      if (l) {
        this.layerContainerOriginPx.y -= l;
        this.minPx.y -= l;
        this.maxPx.y -= l
      }
      this.applyTransform();
      var d,
      c,
      e;
      for (c = 0, e = this.layers.length; c < e; ++c) {
        d = this.layers[c];
        if (d.visibility && (d === this.baseLayer || d.inRange)) {
          d.moveByPx(m, l);
          d.events.triggerEvent('move')
        }
      }
      this.events.triggerEvent('move')
    }
  },
  adjustZoom: function (f) {
    if (this.baseLayer && this.baseLayer.wrapDateLine) {
      var c,
      a = this.baseLayer.resolutions,
      b = this.getMaxExtent().getWidth() / this.size.w;
      if (this.getResolutionForZoom(f) > b) {
        if (this.fractionalZoom) {
          f = this.getZoomForResolution(b)
        } else {
          for (var d = f | 0, e = a.length; d < e; ++d) {
            if (a[d] <= b) {
              f = d;
              break
            }
          }
        }
      }
    }
    return f
  },
  getMinZoom: function () {
    return this.adjustZoom(0)
  },
  moveTo: function (h, b, e) {
    if (h != null && !(h instanceof OpenLayers.LonLat)) {
      h = new OpenLayers.LonLat(h)
    }
    if (!e) {
      e = {
      }
    }
    if (b != null) {
      b = parseFloat(b);
      if (!this.fractionalZoom) {
        b = Math.round(b)
      }
    }
    var m = b;
    b = this.adjustZoom(b);
    if (b !== m) {
      h = this.getCenter()
    }
    var p = e.dragging || this.dragging;
    var k = e.forceZoomChange;
    if (!this.getCachedCenter() && !this.isValidLonLat(h)) {
      h = this.maxExtent.getCenterLonLat();
      this.center = h.clone()
    }
    if (this.restrictedExtent != null) {
      if (h == null) {
        h = this.center
      }
      if (b == null) {
        b = this.getZoom()
      }
      var q = this.getResolutionForZoom(b);
      var n = this.calculateBounds(h, q);
      if (!this.restrictedExtent.containsBounds(n)) {
        var w = this.restrictedExtent.getCenterLonLat();
        if (n.getWidth() > this.restrictedExtent.getWidth()) {
          h = new OpenLayers.LonLat(w.lon, h.lat)
        } else {
          if (n.left < this.restrictedExtent.left) {
            h = h.add(this.restrictedExtent.left - n.left, 0)
          } else {
            if (n.right > this.restrictedExtent.right) {
              h = h.add(this.restrictedExtent.right - n.right, 0)
            }
          }
        }
        if (n.getHeight() > this.restrictedExtent.getHeight()) {
          h = new OpenLayers.LonLat(h.lon, w.lat)
        } else {
          if (n.bottom < this.restrictedExtent.bottom) {
            h = h.add(0, this.restrictedExtent.bottom - n.bottom)
          } else {
            if (n.top > this.restrictedExtent.top) {
              h = h.add(0, this.restrictedExtent.top - n.top)
            }
          }
        }
      }
    }
    var l = k || ((this.isValidZoomLevel(b)) && (b != this.getZoom()));
    var g = (this.isValidLonLat(h)) && (!h.equals(this.center));
    if (l || g || p) {
      p || this.events.triggerEvent('movestart', {
        zoomChanged: l
      });
      if (g) {
        if (!l && this.center) {
          this.centerLayerContainer(h)
        }
        this.center = h.clone()
      }
      var x = l ? this.getResolutionForZoom(b)  : this.getResolution();
      if (l || this.layerContainerOrigin == null) {
        this.layerContainerOrigin = this.getCachedCenter();
        this.layerContainerOriginPx.x = 0;
        this.layerContainerOriginPx.y = 0;
        this.applyTransform();
        var o = this.getMaxExtent({
          restricted: true
        });
        var d = o.getCenterLonLat();
        var j = this.center.lon - d.lon;
        var c = d.lat - this.center.lat;
        var u = Math.round(o.getWidth() / x);
        var t = Math.round(o.getHeight() / x);
        this.minPx = {
          x: (this.size.w - u) / 2 - j / x,
          y: (this.size.h - t) / 2 - c / x
        };
        this.maxPx = {
          x: this.minPx.x + Math.round(o.getWidth() / x),
          y: this.minPx.y + Math.round(o.getHeight() / x)
        }
      }
      if (l) {
        this.zoom = b;
        this.resolution = x
      }
      var f = this.getExtent();
      if (this.baseLayer.visibility) {
        this.baseLayer.moveTo(f, l, e.dragging);
        e.dragging || this.baseLayer.events.triggerEvent('moveend', {
          zoomChanged: l
        })
      }
      f = this.baseLayer.getExtent();
      for (var r = this.layers.length - 1; r >= 0; --r) {
        var v = this.layers[r];
        if (v !== this.baseLayer && !v.isBaseLayer) {
          var a = v.calculateInRange();
          if (v.inRange != a) {
            v.inRange = a;
            if (!a) {
              v.display(false)
            }
            this.events.triggerEvent('changelayer', {
              layer: v,
              property: 'visibility'
            })
          }
          if (a && v.visibility) {
            v.moveTo(f, l, e.dragging);
            e.dragging || v.events.triggerEvent('moveend', {
              zoomChanged: l
            })
          }
        }
      }
      this.events.triggerEvent('move');
      p || this.events.triggerEvent('moveend');
      if (l) {
        for (var r = 0, s = this.popups.length; r < s; r++) {
          this.popups[r].updatePosition()
        }
        this.events.triggerEvent('zoomend')
      }
    }
  },
  centerLayerContainer: function (c) {
    var d = this.getViewPortPxFromLonLat(this.layerContainerOrigin);
    var g = this.getViewPortPxFromLonLat(c);
    if ((d != null) && (g != null)) {
      var a = this.layerContainerOriginPx.x;
      var b = this.layerContainerOriginPx.y;
      var f = Math.round(d.x - g.x);
      var e = Math.round(d.y - g.y);
      this.applyTransform((this.layerContainerOriginPx.x = f), (this.layerContainerOriginPx.y = e));
      var i = a - f;
      var h = b - e;
      this.minPx.x -= i;
      this.maxPx.x -= i;
      this.minPx.y -= h;
      this.maxPx.y -= h
    }
  },
  isValidZoomLevel: function (a) {
    return ((a != null) && (a >= 0) && (a < this.getNumZoomLevels()))
  },
  isValidLonLat: function (d) {
    var c = false;
    if (d != null) {
      var a = this.getMaxExtent();
      var b = this.baseLayer.wrapDateLine && a;
      c = a.containsLonLat(d, {
        worldBounds: b
      })
    }
    return c
  },
  getProjection: function () {
    var a = this.getProjectionObject();
    return a ? a.getCode()  : null
  },
  getProjectionObject: function () {
    var a = null;
    if (this.baseLayer != null) {
      a = this.baseLayer.projection
    }
    return a
  },
  getMaxResolution: function () {
    var a = null;
    if (this.baseLayer != null) {
      a = this.baseLayer.maxResolution
    }
    return a
  },
  getMaxExtent: function (b) {
    var a = null;
    if (b && b.restricted && this.restrictedExtent) {
      a = this.restrictedExtent
    } else {
      if (this.baseLayer != null) {
        a = this.baseLayer.maxExtent
      }
    }
    return a
  },
  getNumZoomLevels: function () {
    var a = null;
    if (this.baseLayer != null) {
      a = this.baseLayer.numZoomLevels
    }
    return a
  },
  getExtent: function () {
    var a = null;
    if (this.baseLayer != null) {
      a = this.baseLayer.getExtent()
    }
    return a
  },
  getResolution: function () {
    var a = null;
    if (this.baseLayer != null) {
      a = this.baseLayer.getResolution()
    } else {
      if (this.allOverlays === true && this.layers.length > 0) {
        a = this.layers[0].getResolution()
      }
    }
    return a
  },
  getUnits: function () {
    var a = null;
    if (this.baseLayer != null) {
      a = this.baseLayer.units
    }
    return a
  },
  getScale: function () {
    var c = null;
    if (this.baseLayer != null) {
      var b = this.getResolution();
      var a = this.baseLayer.units;
      c = OpenLayers.Util.getScaleFromResolution(b, a)
    }
    return c
  },
  getZoomForExtent: function (c, b) {
    var a = null;
    if (this.baseLayer != null) {
      a = this.baseLayer.getZoomForExtent(c, b)
    }
    return a
  },
  getResolutionForZoom: function (b) {
    var a = null;
    if (this.baseLayer) {
      a = this.baseLayer.getResolutionForZoom(b)
    }
    return a
  },
  getZoomForResolution: function (a, c) {
    var b = null;
    if (this.baseLayer != null) {
      b = this.baseLayer.getZoomForResolution(a, c)
    }
    return b
  },
  zoomTo: function (g, h) {
    var b = this;
    if (b.isValidZoomLevel(g)) {
      if (b.baseLayer.wrapDateLine) {
        g = b.adjustZoom(g)
      }
      if (b.zoomTween) {
        var e = b.getResolution(),
        f = b.getResolutionForZoom(g),
        c = {
          scale: 1
        },
        d = {
          scale: e / f
        };
        if (b.zoomTween.playing && b.zoomTween.duration < 3 * b.zoomDuration) {
          b.zoomTween.finish = {
            scale: b.zoomTween.finish.scale * d.scale
          }
        } else {
          if (!h) {
            var i = b.getSize();
            h = {
              x: i.w / 2,
              y: i.h / 2
            }
          }
          b.zoomTween.start(c, d, b.zoomDuration, {
            minFrameRate: 50,
            callbacks: {
              eachStep: function (l) {
                var n = b.layerContainerOriginPx,
                m = l.scale,
                k = ((m - 1) * (n.x - h.x)) | 0,
                j = ((m - 1) * (n.y - h.y)) | 0;
                b.applyTransform(n.x + k, n.y + j, m)
              },
              done: function (l) {
                b.applyTransform();
                var j = b.getResolution() / l.scale,
                k = b.getZoomForResolution(j, true);
                b.moveTo(b.getZoomTargetCenter(h, j), k, true)
              }
            }
          })
        }
      } else {
        var a = h ? b.getZoomTargetCenter(h, b.getResolutionForZoom(g))  : null;
        b.setCenter(a, g)
      }
    }
  },
  zoomIn: function () {
    this.zoomTo(this.getZoom() + 1)
  },
  zoomOut: function () {
    this.zoomTo(this.getZoom() - 1)
  },
  zoomToExtent: function (d, c) {
    if (!(d instanceof OpenLayers.Bounds)) {
      d = new OpenLayers.Bounds(d)
    }
    var b = d.getCenterLonLat();
    if (this.baseLayer.wrapDateLine) {
      var a = this.getMaxExtent();
      d = d.clone();
      while (d.right < d.left) {
        d.right += a.getWidth()
      }
      b = d.getCenterLonLat().wrapDateLine(a)
    }
    this.setCenter(b, this.getZoomForExtent(d, c))
  },
  zoomToMaxExtent: function (c) {
    var b = (c) ? c.restricted : true;
    var a = this.getMaxExtent({
      restricted: b
    });
    this.zoomToExtent(a)
  },
  zoomToScale: function (g, d) {
    var b = OpenLayers.Util.getResolutionFromScale(g, this.baseLayer.units);
    var e = (this.size.w * b) / 2;
    var f = (this.size.h * b) / 2;
    var a = this.getCachedCenter();
    var c = new OpenLayers.Bounds(a.lon - e, a.lat - f, a.lon + e, a.lat + f);
    this.zoomToExtent(c, d)
  },
  getLonLatFromViewPortPx: function (a) {
    var b = null;
    if (this.baseLayer != null) {
      b = this.baseLayer.getLonLatFromViewPortPx(a)
    }
    return b
  },
  getViewPortPxFromLonLat: function (b) {
    var a = null;
    if (this.baseLayer != null) {
      a = this.baseLayer.getViewPortPxFromLonLat(b)
    }
    return a
  },
  getZoomTargetCenter: function (f, c) {
    var e = null,
    d = this.getSize(),
    b = d.w / 2 - f.x,
    a = f.y - d.h / 2,
    g = this.getLonLatFromPixel(f);
    if (g) {
      e = new OpenLayers.LonLat(g.lon + b * c, g.lat + a * c)
    }
    return e
  },
  getLonLatFromPixel: function (a) {
    return this.getLonLatFromViewPortPx(a)
  },
  getPixelFromLonLat: function (b) {
    var a = this.getViewPortPxFromLonLat(b);
    a.x = Math.round(a.x);
    a.y = Math.round(a.y);
    return a
  },
  getGeodesicPixelSize: function (g) {
    var d = g ? this.getLonLatFromPixel(g)  : (this.getCachedCenter() || new OpenLayers.LonLat(0, 0));
    var e = this.getResolution();
    var c = d.add( - e / 2, 0);
    var i = d.add(e / 2, 0);
    var b = d.add(0, - e / 2);
    var f = d.add(0, e / 2);
    var h = new OpenLayers.Projection('EPSG:4326');
    var a = this.getProjectionObject() || h;
    if (!a.equals(h)) {
      c.transform(a, h);
      i.transform(a, h);
      b.transform(a, h);
      f.transform(a, h)
    }
    return new OpenLayers.Size(OpenLayers.Util.distVincenty(c, i), OpenLayers.Util.distVincenty(b, f))
  },
  getViewPortPxFromLayerPx: function (d) {
    var c = null;
    if (d != null) {
      var b = this.layerContainerOriginPx.x;
      var a = this.layerContainerOriginPx.y;
      c = d.add(b, a)
    }
    return c
  },
  getLayerPxFromViewPortPx: function (c) {
    var d = null;
    if (c != null) {
      var b = - this.layerContainerOriginPx.x;
      var a = - this.layerContainerOriginPx.y;
      d = c.add(b, a);
      if (isNaN(d.x) || isNaN(d.y)) {
        d = null
      }
    }
    return d
  },
  getLonLatFromLayerPx: function (a) {
    a = this.getViewPortPxFromLayerPx(a);
    return this.getLonLatFromViewPortPx(a)
  },
  getLayerPxFromLonLat: function (b) {
    var a = this.getPixelFromLonLat(b);
    return this.getLayerPxFromViewPortPx(a)
  },
  applyTransform: function (g, f, d) {
    d = d || 1;
    var h = this.layerContainerOriginPx,
    a = d !== 1;
    g = g || h.x;
    f = f || h.y;
    var b = this.layerContainerDiv.style,
    c = this.applyTransform.transform,
    i = this.applyTransform.template;
    if (c === undefined) {
      c = OpenLayers.Util.vendorPrefix.style('transform');
      this.applyTransform.transform = c;
      if (c) {
        var e = OpenLayers.Element.getStyle(this.viewPortDiv, OpenLayers.Util.vendorPrefix.css('transform'));
        if (!e || e !== 'none') {
          i = [
            'translate3d(',
            ',0) ',
            'scale3d(',
            ',1)'
          ];
          b[c] = [
            i[0],
            '0,0',
            i[1]
          ].join('')
        }
        if (!i || !~b[c].indexOf(i[0])) {
          i = [
            'translate(',
            ') ',
            'scale(',
            ')'
          ]
        }
        this.applyTransform.template = i
      }
    }
    if (c !== null && (i[0] === 'translate3d(' || a === true)) {
      if (a === true && i[0] === 'translate(') {
        g -= h.x;
        f -= h.y;
        b.left = h.x + 'px';
        b.top = h.y + 'px'
      }
      b[c] = [
        i[0],
        g,
        'px,',
        f,
        'px',
        i[1],
        i[2],
        d,
        ',',
        d,
        i[3]
      ].join('')
    } else {
      b.left = g + 'px';
      b.top = f + 'px';
      if (c !== null) {
        b[c] = ''
      }
    }
  },
  CLASS_NAME: 'OpenLayers.Map'
});
OpenLayers.Map.TILE_WIDTH = 256; OpenLayers.Map.TILE_HEIGHT = 256; OpenLayers.Layer = OpenLayers.Class({
  id: null,
  name: null,
  div: null,
  opacity: 1,
  alwaysInRange: null,
  RESOLUTION_PROPERTIES: [
    'scales',
    'resolutions',
    'maxScale',
    'minScale',
    'maxResolution',
    'minResolution',
    'numZoomLevels',
    'maxZoomLevel'
  ],
  events: null,
  map: null,
  isBaseLayer: false,
  alpha: false,
  displayInLayerSwitcher: true,
  visibility: true,
  attribution: null,
  inRange: false,
  imageSize: null,
  options: null,
  eventListeners: null,
  gutter: 0,
  projection: null,
  units: null,
  scales: null,
  resolutions: null,
  maxExtent: null,
  minExtent: null,
  maxResolution: null,
  minResolution: null,
  numZoomLevels: null,
  minScale: null,
  maxScale: null,
  displayOutsideMaxExtent: false,
  wrapDateLine: false,
  metadata: null,
  initialize: function (b, a) {
    this.metadata = {
    };
    a = OpenLayers.Util.extend({
    }, a);
    if (this.alwaysInRange != null) {
      a.alwaysInRange = this.alwaysInRange
    }
    this.addOptions(a);
    this.name = b;
    if (this.id == null) {
      this.id = OpenLayers.Util.createUniqueID(this.CLASS_NAME + '_');
      this.div = OpenLayers.Util.createDiv(this.id);
      this.div.style.width = '100%';
      this.div.style.height = '100%';
      this.div.dir = 'ltr';
      this.events = new OpenLayers.Events(this, this.div);
      if (this.eventListeners instanceof Object) {
        this.events.on(this.eventListeners)
      }
    }
  },
  destroy: function (a) {
    if (a == null) {
      a = true
    }
    if (this.map != null) {
      this.map.removeLayer(this, a)
    }
    this.projection = null;
    this.map = null;
    this.name = null;
    this.div = null;
    this.options = null;
    if (this.events) {
      if (this.eventListeners) {
        this.events.un(this.eventListeners)
      }
      this.events.destroy()
    }
    this.eventListeners = null;
    this.events = null
  },
  clone: function (a) {
    if (a == null) {
      a = new OpenLayers.Layer(this.name, this.getOptions())
    }
    OpenLayers.Util.applyDefaults(a, this);
    a.map = null;
    return a
  },
  getOptions: function () {
    var a = {
    };
    for (var b in this.options) {
      a[b] = this[b]
    }
    return a
  },
  setName: function (a) {
    if (a != this.name) {
      this.name = a;
      if (this.map != null) {
        this.map.events.triggerEvent('changelayer', {
          layer: this,
          property: 'name'
        })
      }
    }
  },
  addOptions: function (e, a) {
    if (this.options == null) {
      this.options = {
      }
    }
    if (e) {
      if (typeof e.projection == 'string') {
        e.projection = new OpenLayers.Projection(e.projection)
      }
      if (e.projection) {
        OpenLayers.Util.applyDefaults(e, OpenLayers.Projection.defaults[e.projection.getCode()])
      }
      if (e.maxExtent && !(e.maxExtent instanceof OpenLayers.Bounds)) {
        e.maxExtent = new OpenLayers.Bounds(e.maxExtent)
      }
      if (e.minExtent && !(e.minExtent instanceof OpenLayers.Bounds)) {
        e.minExtent = new OpenLayers.Bounds(e.minExtent)
      }
    }
    OpenLayers.Util.extend(this.options, e);
    OpenLayers.Util.extend(this, e);
    if (this.projection && this.projection.getUnits()) {
      this.units = this.projection.getUnits()
    }
    if (this.map) {
      var b = this.map.getResolution();
      var c = this.RESOLUTION_PROPERTIES.concat(['projection',
      'units',
      'minExtent',
      'maxExtent']);
      for (var d in e) {
        if (e.hasOwnProperty(d) && OpenLayers.Util.indexOf(c, d) >= 0) {
          this.initResolutions();
          if (a && this.map.baseLayer === this) {
            this.map.setCenter(this.map.getCenter(), this.map.getZoomForResolution(b), false, true);
            this.map.events.triggerEvent('changebaselayer', {
              layer: this
            })
          }
          break
        }
      }
    }
  },
  onMapResize: function () {
  },
  redraw: function () {
    var b = false;
    if (this.map) {
      this.inRange = this.calculateInRange();
      var c = this.getExtent();
      if (c && this.inRange && this.visibility) {
        var a = true;
        this.moveTo(c, a, false);
        this.events.triggerEvent('moveend', {
          zoomChanged: a
        });
        b = true
      }
    }
    return b
  },
  moveTo: function (b, a, c) {
    var d = this.visibility;
    if (!this.isBaseLayer) {
      d = d && this.inRange
    }
    this.display(d)
  },
  moveByPx: function (b, a) {
  },
  setMap: function (b) {
    if (this.map == null) {
      this.map = b;
      this.maxExtent = this.maxExtent || this.map.maxExtent;
      this.minExtent = this.minExtent || this.map.minExtent;
      this.projection = this.projection || this.map.projection;
      if (typeof this.projection == 'string') {
        this.projection = new OpenLayers.Projection(this.projection)
      }
      this.units = this.projection.getUnits() || this.units || this.map.units;
      this.initResolutions();
      if (!this.isBaseLayer) {
        this.inRange = this.calculateInRange();
        var a = ((this.visibility) && (this.inRange));
        this.div.style.display = a ? '' : 'none'
      }
      this.setTileSize()
    }
  },
  afterAdd: function () {
  },
  removeMap: function (a) {
  },
  getImageSize: function (a) {
    return (this.imageSize || this.tileSize)
  },
  setTileSize: function (a) {
    var b = (a) ? a : ((this.tileSize) ? this.tileSize : this.map.getTileSize());
    this.tileSize = b;
    if (this.gutter) {
      this.imageSize = new OpenLayers.Size(b.w + (2 * this.gutter), b.h + (2 * this.gutter))
    }
  },
  getVisibility: function () {
    return this.visibility
  },
  setVisibility: function (a) {
    if (a != this.visibility) {
      this.visibility = a;
      this.display(a);
      this.redraw();
      if (this.map != null) {
        this.map.events.triggerEvent('changelayer', {
          layer: this,
          property: 'visibility'
        })
      }
      this.events.triggerEvent('visibilitychanged')
    }
  },
  display: function (a) {
    if (a != (this.div.style.display != 'none')) {
      this.div.style.display = (a && this.calculateInRange()) ? 'block' : 'none'
    }
  },
  calculateInRange: function () {
    var b = false;
    if (this.alwaysInRange) {
      b = true
    } else {
      if (this.map) {
        var a = this.map.getResolution();
        b = ((a >= this.minResolution) && (a <= this.maxResolution))
      }
    }
    return b
  },
  setIsBaseLayer: function (a) {
    if (a != this.isBaseLayer) {
      this.isBaseLayer = a;
      if (this.map != null) {
        this.map.events.triggerEvent('changebaselayer', {
          layer: this
        })
      }
    }
  },
  initResolutions: function () {
    var e,
    a,
    h;
    var f = {
    },
    d = true;
    for (e = 0, a = this.RESOLUTION_PROPERTIES.length; e < a; e++) {
      h = this.RESOLUTION_PROPERTIES[e];
      f[h] = this.options[h];
      if (d && this.options[h]) {
        d = false
      }
    }
    if (this.options.alwaysInRange == null) {
      this.alwaysInRange = d
    }
    if (f.resolutions == null) {
      f.resolutions = this.resolutionsFromScales(f.scales)
    }
    if (f.resolutions == null) {
      f.resolutions = this.calculateResolutions(f)
    }
    if (f.resolutions == null) {
      for (e = 0, a = this.RESOLUTION_PROPERTIES.length;
      e < a; e++) {
        h = this.RESOLUTION_PROPERTIES[e];
        f[h] = this.options[h] != null ? this.options[h] : this.map[h]
      }
      if (f.resolutions == null) {
        f.resolutions = this.resolutionsFromScales(f.scales)
      }
      if (f.resolutions == null) {
        f.resolutions = this.calculateResolutions(f)
      }
    }
    var c;
    if (this.options.maxResolution && this.options.maxResolution !== 'auto') {
      c = this.options.maxResolution
    }
    if (this.options.minScale) {
      c = OpenLayers.Util.getResolutionFromScale(this.options.minScale, this.units)
    }
    var b;
    if (this.options.minResolution && this.options.minResolution !== 'auto') {
      b = this.options.minResolution
    }
    if (this.options.maxScale) {
      b = OpenLayers.Util.getResolutionFromScale(this.options.maxScale, this.units)
    }
    if (f.resolutions) {
      f.resolutions.sort(function (j, i) {
        return (i - j)
      });
      if (!c) {
        c = f.resolutions[0]
      }
      if (!b) {
        var g = f.resolutions.length - 1;
        b = f.resolutions[g]
      }
    }
    this.resolutions = f.resolutions;
    if (this.resolutions) {
      a = this.resolutions.length;
      this.scales = new Array(a);
      for (e = 0; e < a; e++) {
        this.scales[e] = OpenLayers.Util.getScaleFromResolution(this.resolutions[e], this.units)
      }
      this.numZoomLevels = a
    }
    this.minResolution = b;
    if (b) {
      this.maxScale = OpenLayers.Util.getScaleFromResolution(b, this.units)
    }
    this.maxResolution = c;
    if (c) {
      this.minScale = OpenLayers.Util.getScaleFromResolution(c, this.units)
    }
  },
  resolutionsFromScales: function (d) {
    if (d == null) {
      return
    }
    var b,
    c,
    a;
    a = d.length;
    b = new Array(a);
    for (c = 0; c < a; c++) {
      b[c] = OpenLayers.Util.getResolutionFromScale(d[c], this.units)
    }
    return b
  },
  calculateResolutions: function (k) {
    var l,
    j,
    g;
    var m = k.maxResolution;
    if (k.minScale != null) {
      m = OpenLayers.Util.getResolutionFromScale(k.minScale, this.units)
    } else {
      if (m == 'auto' && this.maxExtent != null) {
        l = this.map.getSize();
        j = this.maxExtent.getWidth() / l.w;
        g = this.maxExtent.getHeight() / l.h;
        m = Math.max(j, g)
      }
    }
    var f = k.minResolution;
    if (k.maxScale != null) {
      f = OpenLayers.Util.getResolutionFromScale(k.maxScale, this.units)
    } else {
      if (k.minResolution == 'auto' && this.minExtent != null) {
        l = this.map.getSize();
        j = this.minExtent.getWidth() / l.w;
        g = this.minExtent.getHeight() / l.h;
        f = Math.max(j, g)
      }
    }
    if (typeof m !== 'number' && typeof f !== 'number' && this.maxExtent != null) {
      var n = this.map.getTileSize();
      m = Math.max(this.maxExtent.getWidth() / n.w, this.maxExtent.getHeight() / n.h)
    }
    var a = k.maxZoomLevel;
    var b = k.numZoomLevels;
    if (typeof f === 'number' && typeof m === 'number' && b === undefined) {
      var h = m / f;
      b = Math.floor(Math.log(h) / Math.log(2)) + 1
    } else {
      if (b === undefined && a != null) {
        b = a + 1
      }
    }
    if (typeof b !== 'number' || b <= 0 || (typeof m !== 'number' && typeof f !== 'number')) {
      return
    }
    var d = new Array(b);
    var c = 2;
    if (typeof f == 'number' && typeof m == 'number') {
      c = Math.pow((m / f), (1 / (b - 1)))
    }
    var e;
    if (typeof m === 'number') {
      for (e = 0; e < b; e++) {
        d[e] = m / Math.pow(c, e)
      }
    } else {
      for (e = 0;
      e < b; e++) {
        d[b - 1 - e] = f * Math.pow(c, e)
      }
    }
    return d
  },
  getResolution: function () {
    var a = this.map.getZoom();
    return this.getResolutionForZoom(a)
  },
  getExtent: function () {
    return this.map.calculateBounds()
  },
  getZoomForExtent: function (b, c) {
    var d = this.map.getSize();
    var a = Math.max(b.getWidth() / d.w, b.getHeight() / d.h);
    return this.getZoomForResolution(a, c)
  },
  getDataExtent: function () {
  },
  getResolutionForZoom: function (c) {
    c = Math.max(0, Math.min(c, this.resolutions.length - 1));
    var b;
    if (this.map.fractionalZoom) {
      var a = Math.floor(c);
      var d = Math.ceil(c);
      b = this.resolutions[a] - ((c - a) * (this.resolutions[a] - this.resolutions[d]))
    } else {
      b = this.resolutions[Math.round(c)]
    }
    return b
  },
  getZoomForResolution: function (e, b) {
    var n,
    f,
    g;
    if (this.map.fractionalZoom) {
      var k = 0;
      var c = this.resolutions.length - 1;
      var d = this.resolutions[k];
      var a = this.resolutions[c];
      var j;
      for (f = 0, g = this.resolutions.length; f < g; ++f) {
        j = this.resolutions[f];
        if (j >= e) {
          d = j;
          k = f
        }
        if (j <= e) {
          a = j;
          c = f;
          break
        }
      }
      var h = d - a;
      if (h > 0) {
        n = k + ((d - e) / h)
      } else {
        n = k
      }
    } else {
      var l;
      var m = Number.POSITIVE_INFINITY;
      for (f = 0, g = this.resolutions.length; f < g; f++) {
        if (b) {
          l = Math.abs(this.resolutions[f] - e);
          if (l > m) {
            break
          }
          m = l
        } else {
          if (this.resolutions[f] < e) {
            break
          }
        }
      }
      n = Math.max(0, f - 1)
    }
    return n
  },
  getLonLatFromViewPortPx: function (b) {
    var d = null;
    var f = this.map;
    if (b != null && f.minPx) {
      var c = f.getResolution();
      var a = f.getMaxExtent({
        restricted: true
      });
      var g = (b.x - f.minPx.x) * c + a.left;
      var e = (f.minPx.y - b.y) * c + a.top;
      d = new OpenLayers.LonLat(g, e);
      if (this.wrapDateLine) {
        d = d.wrapDateLine(this.maxExtent)
      }
    }
    return d
  },
  getViewPortPxFromLonLat: function (d, a) {
    var b = null;
    if (d != null) {
      a = a || this.map.getResolution();
      var c = this.map.calculateBounds(null, a);
      b = new OpenLayers.Pixel((1 / a * (d.lon - c.left)), (1 / a * (c.top - d.lat)))
    }
    return b
  },
  setOpacity: function (b) {
    if (b != this.opacity) {
      this.opacity = b;
      var f = this.div.childNodes;
      for (var d = 0, a = f.length; d < a; ++d) {
        var c = f[d].firstChild || f[d];
        var e = f[d].lastChild;
        if (e && e.nodeName.toLowerCase() === 'iframe') {
          c = e.parentNode
        }
        OpenLayers.Util.modifyDOMElement(c, null, null, null, null, null, null, b)
      }
      if (this.map != null) {
        this.map.events.triggerEvent('changelayer', {
          layer: this,
          property: 'opacity'
        })
      }
    }
  },
  getZIndex: function () {
    return this.div.style.zIndex
  },
  setZIndex: function (a) {
    this.div.style.zIndex = a
  },
  adjustBounds: function (b) {
    if (this.gutter) {
      var a = this.gutter * this.map.getResolution();
      b = new OpenLayers.Bounds(b.left - a, b.bottom - a, b.right + a, b.top + a)
    }
    if (this.wrapDateLine) {
      var c = {
        rightTolerance: this.getResolution(),
        leftTolerance: this.getResolution()
      };
      b = b.wrapDateLine(this.maxExtent, c)
    }
    return b
  },
  CLASS_NAME: 'OpenLayers.Layer'
}); OpenLayers.Renderer = OpenLayers.Class({
  container: null,
  root: null,
  extent: null,
  locked: false,
  size: null,
  resolution: null,
  map: null,
  featureDx: 0,
  initialize: function (a, b) {
    this.container = OpenLayers.Util.getElement(a);
    OpenLayers.Util.extend(this, b)
  },
  destroy: function () {
    this.container = null;
    this.extent = null;
    this.size = null;
    this.resolution = null;
    this.map = null
  },
  supported: function () {
    return false
  },
  setExtent: function (b, c) {
    this.extent = b.clone();
    if (this.map.baseLayer && this.map.baseLayer.wrapDateLine) {
      var a = b.getWidth() / this.map.getExtent().getWidth(),
      b = b.scale(1 / a);
      this.extent = b.wrapDateLine(this.map.getMaxExtent()).scale(a)
    }
    if (c) {
      this.resolution = null
    }
    return true
  },
  setSize: function (a) {
    this.size = a.clone();
    this.resolution = null
  },
  getResolution: function () {
    this.resolution = this.resolution || this.map.getResolution();
    return this.resolution
  },
  drawFeature: function (i, b) {
    if (b == null) {
      b = i.style
    }
    if (i.geometry) {
      var a = i.geometry.getBounds();
      if (a) {
        var h;
        if (this.map.baseLayer && this.map.baseLayer.wrapDateLine) {
          h = this.map.getMaxExtent()
        }
        if (!a.intersectsBounds(this.extent, {
          worldBounds: h
        })) {
          b = {
            display: 'none'
          }
        } else {
          this.calculateFeatureDx(a, h)
        }
        var c = this.drawGeometry(i.geometry, b, i.id);
        if (b.display != 'none' && b.label && c !== false) {
          var g = i.geometry.getCentroid();
          if (b.labelXOffset || b.labelYOffset) {
            var e = isNaN(b.labelXOffset) ? 0 : b.labelXOffset;
            var d = isNaN(b.labelYOffset) ? 0 : b.labelYOffset;
            var f = this.getResolution();
            g.move(e * f, d * f)
          }
          this.drawText(i.id, b, g)
        } else {
          this.removeText(i.id)
        }
        return c
      }
    }
  },
  calculateFeatureDx: function (e, d) {
    this.featureDx = 0;
    if (d) {
      var f = d.getWidth(),
      b = (this.extent.left + this.extent.right) / 2,
      c = (e.left + e.right) / 2,
      a = Math.round((c - b) / f);
      this.featureDx = a * f
    }
  },
  drawGeometry: function (c, a, b) {
  },
  drawText: function (c, b, a) {
  },
  removeText: function (a) {
  },
  clear: function () {
  },
  getFeatureIdFromEvent: function (a) {
  },
  eraseFeatures: function (d) {
    if (!(OpenLayers.Util.isArray(d))) {
      d = [
        d
      ]
    }
    for (var c = 0, a = d.length; c < a; ++c) {
      var b = d[c];
      this.eraseGeometry(b.geometry, b.id);
      this.removeText(b.id)
    }
  },
  eraseGeometry: function (b, a) {
  },
  moveRoot: function (a) {
  },
  getRenderLayerId: function () {
    return this.container.id
  },
  applyDefaultSymbolizer: function (b) {
    var a = OpenLayers.Util.extend({
    }, OpenLayers.Renderer.defaultSymbolizer);
    if (b.stroke === false) {
      delete a.strokeWidth;
      delete a.strokeColor
    }
    if (b.fill === false) {
      delete a.fillColor
    }
    OpenLayers.Util.extend(a, b);
    return a
  },
  CLASS_NAME: 'OpenLayers.Renderer'
}); OpenLayers.Renderer.defaultSymbolizer = {
  fillColor: '#000000',
  strokeColor: '#000000',
  strokeWidth: 2,
  fillOpacity: 1,
  strokeOpacity: 1,
  pointRadius: 0,
  labelAlign: 'cm'
}; OpenLayers.Renderer.symbol = {
  star: [
    350,
    75,
    379,
    161,
    469,
    161,
    397,
    215,
    423,
    301,
    350,
    250,
    277,
    301,
    303,
    215,
    231,
    161,
    321,
    161,
    350,
    75
  ],
  cross: [
    4,
    0,
    6,
    0,
    6,
    4,
    10,
    4,
    10,
    6,
    6,
    6,
    6,
    10,
    4,
    10,
    4,
    6,
    0,
    6,
    0,
    4,
    4,
    4,
    4,
    0
  ],
  x: [
    0,
    0,
    25,
    0,
    50,
    35,
    75,
    0,
    100,
    0,
    65,
    50,
    100,
    100,
    75,
    100,
    50,
    65,
    25,
    100,
    0,
    100,
    35,
    50,
    0,
    0
  ],
  square: [
    0,
    0,
    0,
    1,
    1,
    1,
    1,
    0,
    0,
    0
  ],
  triangle: [
    0,
    10,
    10,
    10,
    5,
    0,
    0,
    10
  ]
};
OpenLayers.Popup = OpenLayers.Class({
  events: null,
  id: '',
  lonlat: null,
  div: null,
  contentSize: null,
  size: null,
  contentHTML: null,
  backgroundColor: '',
  opacity: '',
  border: '',
  contentDiv: null,
  groupDiv: null,
  closeDiv: null,
  autoSize: false,
  minSize: null,
  maxSize: null,
  displayClass: 'olPopup',
  contentDisplayClass: 'olPopupContent',
  padding: 0,
  disableFirefoxOverflowHack: false,
  fixPadding: function () {
    if (typeof this.padding == 'number') {
      this.padding = new OpenLayers.Bounds(this.padding, this.padding, this.padding, this.padding)
    }
  },
  panMapIfOutOfView: false,
  keepInMap: false,
  closeOnMove: false,
  map: null,
  initialize: function (g, c, f, b, e, d) {
    if (g == null) {
      g = OpenLayers.Util.createUniqueID(this.CLASS_NAME + '_')
    }
    this.id = g;
    this.lonlat = c;
    this.contentSize = (f != null) ? f : new OpenLayers.Size(OpenLayers.Popup.WIDTH, OpenLayers.Popup.HEIGHT);
    if (b != null) {
      this.contentHTML = b
    }
    this.backgroundColor = OpenLayers.Popup.COLOR;
    this.opacity = OpenLayers.Popup.OPACITY;
    this.border = OpenLayers.Popup.BORDER;
    this.div = OpenLayers.Util.createDiv(this.id, null, null, null, null, null, 'hidden');
    this.div.className = this.displayClass;
    var a = this.id + '_GroupDiv';
    this.groupDiv = OpenLayers.Util.createDiv(a, null, null, null, 'relative', null, 'hidden');
    var g = this.div.id + '_contentDiv';
    this.contentDiv = OpenLayers.Util.createDiv(g, null, this.contentSize.clone(), null, 'relative');
    this.contentDiv.className = this.contentDisplayClass;
    this.groupDiv.appendChild(this.contentDiv);
    this.div.appendChild(this.groupDiv);
    if (e) {
      this.addCloseBox(d)
    }
    this.registerEvents()
  },
  destroy: function () {
    this.id = null;
    this.lonlat = null;
    this.size = null;
    this.contentHTML = null;
    this.backgroundColor = null;
    this.opacity = null;
    this.border = null;
    if (this.closeOnMove && this.map) {
      this.map.events.unregister('movestart', this, this.hide)
    }
    this.events.destroy();
    this.events = null;
    if (this.closeDiv) {
      OpenLayers.Event.stopObservingElement(this.closeDiv);
      this.groupDiv.removeChild(this.closeDiv)
    }
    this.closeDiv = null;
    this.div.removeChild(this.groupDiv);
    this.groupDiv = null;
    if (this.map != null) {
      this.map.removePopup(this)
    }
    this.map = null;
    this.div = null;
    this.autoSize = null;
    this.minSize = null;
    this.maxSize = null;
    this.padding = null;
    this.panMapIfOutOfView = null
  },
  draw: function (a) {
    if (a == null) {
      if ((this.lonlat != null) && (this.map != null)) {
        a = this.map.getLayerPxFromLonLat(this.lonlat)
      }
    }
    if (this.closeOnMove) {
      this.map.events.register('movestart', this, this.hide)
    }
    if (!this.disableFirefoxOverflowHack && OpenLayers.BROWSER_NAME == 'firefox') {
      this.map.events.register('movestart', this, function () {
        var b = document.defaultView.getComputedStyle(this.contentDiv, null);
        var c = b.getPropertyValue('overflow');
        if (c != 'hidden') {
          this.contentDiv._oldOverflow = c;
          this.contentDiv.style.overflow = 'hidden'
        }
      });
      this.map.events.register('moveend', this, function () {
        var b = this.contentDiv._oldOverflow;
        if (b) {
          this.contentDiv.style.overflow = b;
          this.contentDiv._oldOverflow = null
        }
      })
    }
    this.moveTo(a);
    if (!this.autoSize && !this.size) {
      this.setSize(this.contentSize)
    }
    this.setBackgroundColor();
    this.setOpacity();
    this.setBorder();
    this.setContentHTML();
    if (this.panMapIfOutOfView) {
      this.panIntoView()
    }
    return this.div
  },
  updatePosition: function () {
    if ((this.lonlat) && (this.map)) {
      var a = this.map.getLayerPxFromLonLat(this.lonlat);
      if (a) {
        this.moveTo(a)
      }
    }
  },
  moveTo: function (a) {
    if ((a != null) && (this.div != null)) {
      this.div.style.left = a.x + 'px';
      this.div.style.top = a.y + 'px'
    }
  },
  visible: function () {
    return OpenLayers.Element.visible(this.div)
  },
  toggle: function () {
    if (this.visible()) {
      this.hide()
    } else {
      this.show()
    }
  },
  show: function () {
    this.div.style.display = '';
    if (this.panMapIfOutOfView) {
      this.panIntoView()
    }
  },
  hide: function () {
    this.div.style.display = 'none'
  },
  setSize: function (c) {
    this.size = c.clone();
    var b = this.getContentDivPadding();
    var a = b.left + b.right;
    var e = b.top + b.bottom;
    this.fixPadding();
    a += this.padding.left + this.padding.right;
    e += this.padding.top + this.padding.bottom;
    if (this.closeDiv) {
      var d = parseInt(this.closeDiv.style.width);
      a += d + b.right
    }
    this.size.w += a;
    this.size.h += e;
    if (OpenLayers.BROWSER_NAME == 'msie') {
      this.contentSize.w += b.left + b.right;
      this.contentSize.h += b.bottom + b.top
    }
    if (this.div != null) {
      this.div.style.width = this.size.w + 'px';
      this.div.style.height = this.size.h + 'px'
    }
    if (this.contentDiv != null) {
      this.contentDiv.style.width = c.w + 'px';
      this.contentDiv.style.height = c.h + 'px'
    }
  },
  updateSize: function () {
    var e = '<div class=\'' + this.contentDisplayClass + '\'>' + this.contentDiv.innerHTML + '</div>';
    var h = (this.map) ? this.map.div : document.body;
    var i = OpenLayers.Util.getRenderedDimensions(e, null, {
      displayClass: this.displayClass,
      containerElement: h
    });
    var g = this.getSafeContentSize(i);
    var f = null;
    if (g.equals(i)) {
      f = i
    } else {
      var b = {
        w: (g.w < i.w) ? g.w : null,
        h: (g.h < i.h) ? g.h : null
      };
      if (b.w && b.h) {
        f = g
      } else {
        var d = OpenLayers.Util.getRenderedDimensions(e, b, {
          displayClass: this.contentDisplayClass,
          containerElement: h
        });
        var c = OpenLayers.Element.getStyle(this.contentDiv, 'overflow');
        if ((c != 'hidden') && (d.equals(g))) {
          var a = OpenLayers.Util.getScrollbarWidth();
          if (b.w) {
            d.h += a
          } else {
            d.w += a
          }
        }
        f = this.getSafeContentSize(d)
      }
    }
    this.setSize(f)
  },
  setBackgroundColor: function (a) {
    if (a != undefined) {
      this.backgroundColor = a
    }
    if (this.div != null) {
      this.div.style.backgroundColor = this.backgroundColor
    }
  },
  setOpacity: function (a) {
    if (a != undefined) {
      this.opacity = a
    }
    if (this.div != null) {
      this.div.style.opacity = this.opacity;
      this.div.style.filter = 'alpha(opacity=' + this.opacity * 100 + ')'
    }
  },
  setBorder: function (a) {
    if (a != undefined) {
      this.border = a
    }
    if (this.div != null) {
      this.div.style.border = this.border
    }
  },
  setContentHTML: function (a) {
    if (a != null) {
      this.contentHTML = a
    }
    if ((this.contentDiv != null) && (this.contentHTML != null) && (this.contentHTML != this.contentDiv.innerHTML)) {
      this.contentDiv.innerHTML = this.contentHTML;
      if (this.autoSize) {
        this.registerImageListeners();
        this.updateSize()
      }
    }
  },
  registerImageListeners: function () {
    var f = function () {
      if (this.popup.id === null) {
        return
      }
      this.popup.updateSize();
      if (this.popup.visible() && this.popup.panMapIfOutOfView) {
        this.popup.panIntoView()
      }
      OpenLayers.Event.stopObserving(this.img, 'load', this.img._onImgLoad)
    };
    var b = this.contentDiv.getElementsByTagName('img');
    for (var e = 0, a = b.length; e < a; e++) {
      var c = b[e];
      if (c.width == 0 || c.height == 0) {
        var d = {
          popup: this,
          img: c
        };
        c._onImgLoad = OpenLayers.Function.bind(f, d);
        OpenLayers.Event.observe(c, 'load', c._onImgLoad)
      }
    }
  },
  getSafeContentSize: function (k) {
    var d = k.clone();
    var i = this.getContentDivPadding();
    var j = i.left + i.right;
    var g = i.top + i.bottom;
    this.fixPadding();
    j += this.padding.left + this.padding.right;
    g += this.padding.top + this.padding.bottom;
    if (this.closeDiv) {
      var c = parseInt(this.closeDiv.style.width);
      j += c + i.right
    }
    if (this.minSize) {
      d.w = Math.max(d.w, (this.minSize.w - j));
      d.h = Math.max(d.h, (this.minSize.h - g))
    }
    if (this.maxSize) {
      d.w = Math.min(d.w, (this.maxSize.w - j));
      d.h = Math.min(d.h, (this.maxSize.h - g))
    }
    if (this.map && this.map.size) {
      var f = 0,
      e = 0;
      if (this.keepInMap && !this.panMapIfOutOfView) {
        var h = this.map.getPixelFromLonLat(this.lonlat);
        switch (this.relativePosition) {
          case 'tr':
            f = h.x;
            e = this.map.size.h - h.y;
            break;
          case 'tl':
            f = this.map.size.w - h.x;
            e = this.map.size.h - h.y;
            break;
          case 'bl':
            f = this.map.size.w - h.x;
            e = h.y;
            break;
          case 'br':
            f = h.x;
            e = h.y;
            break;
          default:
            f = h.x;
            e = this.map.size.h - h.y;
            break
        }
      }
      var a = this.map.size.h - this.map.paddingForPopups.top - this.map.paddingForPopups.bottom - g - e;
      var b = this.map.size.w - this.map.paddingForPopups.left - this.map.paddingForPopups.right - j - f;
      d.w = Math.min(d.w, b);
      d.h = Math.min(d.h, a)
    }
    return d
  },
  getContentDivPadding: function () {
    var a = this._contentDivPadding;
    if (!a) {
      if (this.div.parentNode == null) {
        this.div.style.display = 'none';
        document.body.appendChild(this.div)
      }
      a = new OpenLayers.Bounds(OpenLayers.Element.getStyle(this.contentDiv, 'padding-left'), OpenLayers.Element.getStyle(this.contentDiv, 'padding-bottom'), OpenLayers.Element.getStyle(this.contentDiv, 'padding-right'), OpenLayers.Element.getStyle(this.contentDiv, 'padding-top'));
      this._contentDivPadding = a;
      if (this.div.parentNode == document.body) {
        document.body.removeChild(this.div);
        this.div.style.display = ''
      }
    }
    return a
  },
  addCloseBox: function (c) {
    this.closeDiv = OpenLayers.Util.createDiv(this.id + '_close', null, {
      w: 17,
      h: 17
    });
    this.closeDiv.className = 'olPopupCloseBox';
    var b = this.getContentDivPadding();
    this.closeDiv.style.right = b.right + 'px';
    this.closeDiv.style.top = b.top + 'px';
    this.groupDiv.appendChild(this.closeDiv);
    var a = c || function (d) {
      this.hide();
      OpenLayers.Event.stop(d)
    };
    OpenLayers.Event.observe(this.closeDiv, 'touchend', OpenLayers.Function.bindAsEventListener(a, this));
    OpenLayers.Event.observe(this.closeDiv, 'click', OpenLayers.Function.bindAsEventListener(a, this))
  },
  panIntoView: function () {
    var e = this.map.getSize();
    var d = this.map.getViewPortPxFromLayerPx(new OpenLayers.Pixel(parseInt(this.div.style.left), parseInt(this.div.style.top)));
    var c = d.clone();
    if (d.x < this.map.paddingForPopups.left) {
      c.x = this.map.paddingForPopups.left
    } else {
      if ((d.x + this.size.w) > (e.w - this.map.paddingForPopups.right)) {
        c.x = e.w - this.map.paddingForPopups.right - this.size.w
      }
    }
    if (d.y < this.map.paddingForPopups.top) {
      c.y = this.map.paddingForPopups.top
    } else {
      if ((d.y + this.size.h) > (e.h - this.map.paddingForPopups.bottom)) {
        c.y = e.h - this.map.paddingForPopups.bottom - this.size.h
      }
    }
    var b = d.x - c.x;
    var a = d.y - c.y;
    this.map.pan(b, a)
  },
  registerEvents: function () {
    this.events = new OpenLayers.Events(this, this.div, null, true);
    function a(b) {
      OpenLayers.Event.stop(b, true)
    }
    this.events.on({
      mousedown: this.onmousedown,
      mousemove: this.onmousemove,
      mouseup: this.onmouseup,
      click: this.onclick,
      mouseout: this.onmouseout,
      dblclick: this.ondblclick,
      touchstart: a,
      scope: this
    })
  },
  onmousedown: function (a) {
    this.mousedown = true;
    OpenLayers.Event.stop(a, true)
  },
  onmousemove: function (a) {
    if (this.mousedown) {
      OpenLayers.Event.stop(a, true)
    }
  },
  onmouseup: function (a) {
    if (this.mousedown) {
      this.mousedown = false;
      OpenLayers.Event.stop(a, true)
    }
  },
  onclick: function (a) {
    OpenLayers.Event.stop(a, true)
  },
  onmouseout: function (a) {
    this.mousedown = false
  },
  ondblclick: function (a) {
    OpenLayers.Event.stop(a, true)
  },
  CLASS_NAME: 'OpenLayers.Popup'
}); OpenLayers.Popup.WIDTH = 200; OpenLayers.Popup.HEIGHT = 200; OpenLayers.Popup.COLOR = 'white'; OpenLayers.Popup.OPACITY = 1; OpenLayers.Popup.BORDER = '0px'; OpenLayers.Format = OpenLayers.Class({
  options: null,
  externalProjection: null,
  internalProjection: null,
  data: null,
  keepData: false,
  initialize: function (a) {
    OpenLayers.Util.extend(this, a);
    this.options = a
  },
  destroy: function () {
  },
  read: function (a) {
    throw new Error('Read not implemented.')
  },
  write: function (a) {
    throw new Error('Write not implemented.')
  },
  CLASS_NAME: 'OpenLayers.Format'
}); OpenLayers.Geometry = OpenLayers.Class({
  id: null,
  parent: null,
  bounds: null,
  initialize: function () {
    this.id = OpenLayers.Util.createUniqueID(this.CLASS_NAME + '_')
  },
  destroy: function () {
    this.id = null;
    this.bounds = null
  },
  clone: function () {
    return new OpenLayers.Geometry()
  },
  setBounds: function (a) {
    if (a) {
      this.bounds = a.clone()
    }
  },
  clearBounds: function () {
    this.bounds = null;
    if (this.parent) {
      this.parent.clearBounds()
    }
  },
  extendBounds: function (b) {
    var a = this.getBounds();
    if (!a) {
      this.setBounds(b)
    } else {
      this.bounds.extend(b)
    }
  },
  getBounds: function () {
    if (this.bounds == null) {
      this.calculateBounds()
    }
    return this.bounds
  },
  calculateBounds: function () {
  },
  distanceTo: function (b, a) {
  },
  getVertices: function (a) {
  },
  atPoint: function (e, h, f) {
    var c = false;
    var d = this.getBounds();
    if ((d != null) && (e != null)) {
      var b = (h != null) ? h : 0;
      var a = (f != null) ? f : 0;
      var g = new OpenLayers.Bounds(this.bounds.left - b, this.bounds.bottom - a, this.bounds.right + b, this.bounds.top + a);
      c = g.containsLonLat(e)
    }
    return c
  },
  getLength: function () {
    return 0
  },
  getArea: function () {
    return 0
  },
  getCentroid: function () {
    return null
  },
  toString: function () {
    var a;
    if (OpenLayers.Format && OpenLayers.Format.WKT) {
      a = OpenLayers.Format.WKT.prototype.write(new OpenLayers.Feature.Vector(this))
    } else {
      a = Object.prototype.toString.call(this)
    }
    return a
  },
  CLASS_NAME: 'OpenLayers.Geometry'
}); OpenLayers.Geometry.fromWKT = function (f) {
  var d;
  if (OpenLayers.Format && OpenLayers.Format.WKT) {
    var g = OpenLayers.Geometry.fromWKT.format;
    if (!g) {
      g = new OpenLayers.Format.WKT();
      OpenLayers.Geometry.fromWKT.format = g
    }
    var b = g.read(f);
    if (b instanceof OpenLayers.Feature.Vector) {
      d = b.geometry
    } else {
      if (OpenLayers.Util.isArray(b)) {
        var a = b.length;
        var e = new Array(a);
        for (var c = 0; c < a; ++c) {
          e[c] = b[c].geometry
        }
        d = new OpenLayers.Geometry.Collection(e)
      }
    }
  }
  return d
}; OpenLayers.Geometry.segmentsIntersect = function (a, H, b) {
  var s = b && b.point;
  var z = b && b.tolerance;
  var f = false;
  var B = a.x1 - H.x1;
  var F = a.y1 - H.y1;
  var o = a.x2 - a.x1;
  var w = a.y2 - a.y1;
  var t = H.y2 - H.y1;
  var l = H.x2 - H.x1;
  var D = (t * o) - (l * w);
  var e = (l * F) - (t * B);
  var c = (o * F) - (w * B);
  if (D == 0) {
    if (e == 0 && c == 0) {
      f = true
    }
  } else {
    var E = e / D;
    var C = c / D;
    if (E >= 0 && E <= 1 && C >= 0 && C <= 1) {
      if (!s) {
        f = true
      } else {
        var h = a.x1 + (E * o);
        var g = a.y1 + (E * w);
        f = new OpenLayers.Geometry.Point(h, g)
      }
    }
  }
  if (z) {
    var r;
    if (f) {
      if (s) {
        var n = [
          a,
          H
        ];
        var A,
        h,
        g;
        outer: for (var v = 0; v < 2; ++v) {
          A = n[v];
          for (var u = 1; u < 3; ++u) {
            h = A['x' + u];
            g = A['y' + u];
            r = Math.sqrt(Math.pow(h - f.x, 2) + Math.pow(g - f.y, 2));
            if (r < z) {
              f.x = h;
              f.y = g;
              break outer
            }
          }
        }
      }
    } else {
      var n = [
        a,
        H
      ];
      var q,
      G,
      h,
      g,
      m,
      k;
      outer: for (var v = 0; v < 2; ++v) {
        q = n[v];
        G = n[(v + 1) % 2];
        for (var u = 1;
        u < 3; ++u) {
          m = {
            x: q['x' + u],
            y: q['y' + u]
          };
          k = OpenLayers.Geometry.distanceToSegment(m, G);
          if (k.distance < z) {
            if (s) {
              f = new OpenLayers.Geometry.Point(m.x, m.y)
            } else {
              f = true
            }
            break outer
          }
        }
      }
    }
  }
  return f
}; OpenLayers.Geometry.distanceToSegment = function (b, c) {
  var a = OpenLayers.Geometry.distanceSquaredToSegment(b, c);
  a.distance = Math.sqrt(a.distance);
  return a
}; OpenLayers.Geometry.distanceSquaredToSegment = function (k, d) {
  var c = k.x;
  var j = k.y;
  var b = d.x1;
  var i = d.y1;
  var a = d.x2;
  var f = d.y2;
  var m = a - b;
  var l = f - i;
  var h = ((m * (c - b)) + (l * (j - i))) / (Math.pow(m, 2) + Math.pow(l, 2));
  var g,
  e;
  if (h <= 0) {
    g = b;
    e = i
  } else {
    if (h >= 1) {
      g = a;
      e = f
    } else {
      g = b + h * m;
      e = i + h * l
    }
  }
  return {
    distance: Math.pow(g - c, 2) + Math.pow(e - j, 2),
    x: g,
    y: e,
    along: h
  }
}; OpenLayers.Geometry.Point = OpenLayers.Class(OpenLayers.Geometry, {
  x: null,
  y: null,
  initialize: function (a, b) {
    OpenLayers.Geometry.prototype.initialize.apply(this, arguments);
    this.x = parseFloat(a);
    this.y = parseFloat(b)
  },
  clone: function (a) {
    if (a == null) {
      a = new OpenLayers.Geometry.Point(this.x, this.y)
    }
    OpenLayers.Util.applyDefaults(a, this);
    return a
  },
  calculateBounds: function () {
    this.bounds = new OpenLayers.Bounds(this.x, this.y, this.x, this.y)
  },
  distanceTo: function (f, j) {
    var d = !(j && j.edge === false);
    var a = d && j && j.details;
    var b,
    e,
    h,
    c,
    g,
    i;
    if (f instanceof OpenLayers.Geometry.Point) {
      e = this.x;
      h = this.y;
      c = f.x;
      g = f.y;
      b = Math.sqrt(Math.pow(e - c, 2) + Math.pow(h - g, 2));
      i = !a ? b : {
        x0: e,
        y0: h,
        x1: c,
        y1: g,
        distance: b
      }
    } else {
      i = f.distanceTo(this, j);
      if (a) {
        i = {
          x0: i.x1,
          y0: i.y1,
          x1: i.x0,
          y1: i.y0,
          distance: i.distance
        }
      }
    }
    return i
  },
  equals: function (a) {
    var b = false;
    if (a != null) {
      b = ((this.x == a.x && this.y == a.y) || (isNaN(this.x) && isNaN(this.y) && isNaN(a.x) && isNaN(a.y)))
    }
    return b
  },
  toShortString: function () {
    return (this.x + ', ' + this.y)
  },
  move: function (a, b) {
    this.x = this.x + a;
    this.y = this.y + b;
    this.clearBounds()
  },
  rotate: function (d, b) {
    d *= Math.PI / 180;
    var a = this.distanceTo(b);
    var c = d + Math.atan2(this.y - b.y, this.x - b.x);
    this.x = b.x + (a * Math.cos(c));
    this.y = b.y + (a * Math.sin(c));
    this.clearBounds()
  },
  getCentroid: function () {
    return new OpenLayers.Geometry.Point(this.x, this.y)
  },
  resize: function (c, a, b) {
    b = (b == undefined) ? 1 : b;
    this.x = a.x + (c * b * (this.x - a.x));
    this.y = a.y + (c * (this.y - a.y));
    this.clearBounds();
    return this
  },
  intersects: function (b) {
    var a = false;
    if (b.CLASS_NAME == 'OpenLayers.Geometry.Point') {
      a = this.equals(b)
    } else {
      a = b.intersects(this)
    }
    return a
  },
  transform: function (b, a) {
    if ((b && a)) {
      OpenLayers.Projection.transform(this, b, a);
      this.bounds = null
    }
    return this
  },
  getVertices: function (a) {
    return [this]
  },
  CLASS_NAME: 'OpenLayers.Geometry.Point'
}); OpenLayers.Geometry.Collection = OpenLayers.Class(OpenLayers.Geometry, {
  components: null,
  componentTypes: null,
  initialize: function (a) {
    OpenLayers.Geometry.prototype.initialize.apply(this, arguments);
    this.components = [
    ];
    if (a != null) {
      this.addComponents(a)
    }
  },
  destroy: function () {
    this.components.length = 0;
    this.components = null;
    OpenLayers.Geometry.prototype.destroy.apply(this, arguments)
  },
  clone: function () {
    var geometry = eval('new ' + this.CLASS_NAME + '()');
    for (var i = 0, len = this.components.length; i < len; i++) {
      geometry.addComponent(this.components[i].clone())
    }
    OpenLayers.Util.applyDefaults(geometry, this);
    return geometry
  },
  getComponentsString: function () {
    var b = [
    ];
    for (var c = 0, a = this.components.length; c < a; c++) {
      b.push(this.components[c].toShortString())
    }
    return b.join(',')
  },
  calculateBounds: function () {
    this.bounds = null;
    var d = new OpenLayers.Bounds();
    var c = this.components;
    if (c) {
      for (var b = 0, a = c.length; b < a; b++) {
        d.extend(c[b].getBounds())
      }
    }
    if (d.left != null && d.bottom != null && d.right != null && d.top != null) {
      this.setBounds(d)
    }
  },
  addComponents: function (c) {
    if (!(OpenLayers.Util.isArray(c))) {
      c = [
        c
      ]
    }
    for (var b = 0, a = c.length; b < a; b++) {
      this.addComponent(c[b])
    }
  },
  addComponent: function (b, a) {
    var d = false;
    if (b) {
      if (this.componentTypes == null || (OpenLayers.Util.indexOf(this.componentTypes, b.CLASS_NAME) > - 1)) {
        if (a != null && (a < this.components.length)) {
          var e = this.components.slice(0, a);
          var c = this.components.slice(a, this.components.length);
          e.push(b);
          this.components = e.concat(c)
        } else {
          this.components.push(b)
        }
        b.parent = this;
        this.clearBounds();
        d = true
      }
    }
    return d
  },
  removeComponents: function (b) {
    var c = false;
    if (!(OpenLayers.Util.isArray(b))) {
      b = [
        b
      ]
    }
    for (var a = b.length - 1; a >= 0; --a) {
      c = this.removeComponent(b[a]) || c
    }
    return c
  },
  removeComponent: function (a) {
    OpenLayers.Util.removeItem(this.components, a);
    this.clearBounds();
    return true
  },
  getLength: function () {
    var c = 0;
    for (var b = 0, a = this.components.length; b < a; b++) {
      c += this.components[b].getLength()
    }
    return c
  },
  getArea: function () {
    var c = 0;
    for (var b = 0, a = this.components.length; b < a; b++) {
      c += this.components[b].getArea()
    }
    return c
  },
  getGeodesicArea: function (b) {
    var d = 0;
    for (var c = 0, a = this.components.length; c < a; c++) {
      d += this.components[c].getGeodesicArea(b)
    }
    return d
  },
  getCentroid: function (g) {
    if (!g) {
      return this.components.length && this.components[0].getCentroid()
    }
    var l = this.components.length;
    if (!l) {
      return false
    }
    var b = [
    ];
    var c = [
    ];
    var d = 0;
    var h = Number.MAX_VALUE;
    var m;
    for (var k = 0; k < l; ++k) {
      m = this.components[k];
      var e = m.getArea();
      var f = m.getCentroid(true);
      if (isNaN(e) || isNaN(f.x) || isNaN(f.y)) {
        continue
      }
      b.push(e);
      d += e;
      h = (e < h && e > 0) ? e : h;
      c.push(f)
    }
    l = b.length;
    if (d === 0) {
      for (var k = 0; k < l; ++k) {
        b[k] = 1
      }
      d = b.length
    } else {
      for (var k = 0; k < l; ++k) {
        b[k] /= h
      }
      d /= h
    }
    var j = 0,
    a = 0,
    f,
    e;
    for (var k = 0; k < l; ++k) {
      f = c[k];
      e = b[k];
      j += f.x * e;
      a += f.y * e
    }
    return new OpenLayers.Geometry.Point(j / d, a / d)
  },
  getGeodesicLength: function (b) {
    var d = 0;
    for (var c = 0, a = this.components.length;
    c < a; c++) {
      d += this.components[c].getGeodesicLength(b)
    }
    return d
  },
  move: function (b, d) {
    for (var c = 0, a = this.components.length; c < a; c++) {
      this.components[c].move(b, d)
    }
  },
  rotate: function (d, b) {
    for (var c = 0, a = this.components.length; c < a; ++c) {
      this.components[c].rotate(d, b)
    }
  },
  resize: function (d, a, c) {
    for (var b = 0; b < this.components.length;
    ++b) {
      this.components[b].resize(d, a, c)
    }
    return this
  },
  distanceTo: function (h, j) {
    var c = !(j && j.edge === false);
    var a = c && j && j.details;
    var k,
    d,
    b;
    var e = Number.POSITIVE_INFINITY;
    for (var f = 0, g = this.components.length; f < g; ++f) {
      k = this.components[f].distanceTo(h, j);
      b = a ? k.distance : k;
      if (b < e) {
        e = b;
        d = k;
        if (e == 0) {
          break
        }
      }
    }
    return d
  },
  equals: function (d) {
    var b = true;
    if (!d || !d.CLASS_NAME || (this.CLASS_NAME != d.CLASS_NAME)) {
      b = false
    } else {
      if (!(OpenLayers.Util.isArray(d.components)) || (d.components.length != this.components.length)) {
        b = false
      } else {
        for (var c = 0, a = this.components.length; c < a; ++c) {
          if (!this.components[c].equals(d.components[c])) {
            b = false;
            break
          }
        }
      }
    }
    return b
  },
  transform: function (e, c) {
    if (e && c) {
      for (var d = 0, a = this.components.length; d < a; d++) {
        var b = this.components[d];
        b.transform(e, c)
      }
      this.bounds = null
    }
    return this
  },
  intersects: function (d) {
    var b = false;
    for (var c = 0, a = this.components.length; c < a; ++c) {
      b = d.intersects(this.components[c]);
      if (b) {
        break
      }
    }
    return b
  },
  getVertices: function (b) {
    var c = [
    ];
    for (var d = 0, a = this.components.length; d < a; ++d) {
      Array.prototype.push.apply(c, this.components[d].getVertices(b))
    }
    return c
  },
  CLASS_NAME: 'OpenLayers.Geometry.Collection'
}); OpenLayers.Geometry.MultiPoint = OpenLayers.Class(OpenLayers.Geometry.Collection, {
  componentTypes: [
    'OpenLayers.Geometry.Point'
  ],
  addPoint: function (a, b) {
    this.addComponent(a, b)
  },
  removePoint: function (a) {
    this.removeComponent(a)
  },
  CLASS_NAME: 'OpenLayers.Geometry.MultiPoint'
}); OpenLayers.Geometry.MultiLineString = OpenLayers.Class(OpenLayers.Geometry.Collection, {
  componentTypes: [
    'OpenLayers.Geometry.LineString'
  ],
  split: function (n, s) {
    var g = null;
    var r = s && s.mutual;
    var o,
    a,
    q,
    m,
    b;
    var e = [
    ];
    var p = [
      n
    ];
    for (var f = 0, h = this.components.length; f < h; ++f) {
      a = this.components[f];
      m = false;
      for (var d = 0; d < p.length; ++d) {
        o = a.split(p[d], s);
        if (o) {
          if (r) {
            q = o[0];
            for (var c = 0, l = q.length; c < l; ++c) {
              if (c === 0 && e.length) {
                e[e.length - 1].addComponent(q[c])
              } else {
                e.push(new OpenLayers.Geometry.MultiLineString([q[c]]))
              }
            }
            m = true;
            o = o[1]
          }
          if (o.length) {
            o.unshift(d, 1);
            Array.prototype.splice.apply(p, o);
            break
          }
        }
      }
      if (!m) {
        if (e.length) {
          e[e.length - 1].addComponent(a.clone())
        } else {
          e = [
            new OpenLayers.Geometry.MultiLineString(a.clone())
          ]
        }
      }
    }
    if (e && e.length > 1) {
      m = true
    } else {
      e = [
      ]
    }
    if (p && p.length > 1) {
      b = true
    } else {
      p = [
      ]
    }
    if (m || b) {
      if (r) {
        g = [
          e,
          p
        ]
      } else {
        g = p
      }
    }
    return g
  },
  splitWith: function (n, s) {
    var g = null;
    var r = s && s.mutual;
    var o,
    c,
    q,
    m,
    a,
    e,
    p;
    if (n instanceof OpenLayers.Geometry.LineString) {
      p = [
      ];
      e = [
        n
      ];
      for (var f = 0, h = this.components.length; f < h; ++f) {
        a = false;
        c = this.components[f];
        for (var d = 0; d < e.length; ++d) {
          o = e[d].split(c, s);
          if (o) {
            if (r) {
              q = o[0];
              if (q.length) {
                q.unshift(d, 1);
                Array.prototype.splice.apply(e, q);
                d += q.length - 2
              }
              o = o[1];
              if (o.length === 0) {
                o = [
                  c.clone()
                ]
              }
            }
            for (var b = 0, l = o.length; b < l; ++b) {
              if (b === 0 && p.length) {
                p[p.length - 1].addComponent(o[b])
              } else {
                p.push(new OpenLayers.Geometry.MultiLineString([o[b]]))
              }
            }
            a = true
          }
        }
        if (!a) {
          if (p.length) {
            p[p.length - 1].addComponent(c.clone())
          } else {
            p = [
              new OpenLayers.Geometry.MultiLineString([c.clone()])
            ]
          }
        }
      }
    } else {
      g = n.split(this)
    }
    if (e && e.length > 1) {
      m = true
    } else {
      e = [
      ]
    }
    if (p && p.length > 1) {
      a = true
    } else {
      p = [
      ]
    }
    if (m || a) {
      if (r) {
        g = [
          e,
          p
        ]
      } else {
        g = p
      }
    }
    return g
  },
  CLASS_NAME: 'OpenLayers.Geometry.MultiLineString'
}); OpenLayers.Geometry.Curve = OpenLayers.Class(OpenLayers.Geometry.MultiPoint, {
  componentTypes: [
    'OpenLayers.Geometry.Point'
  ],
  getLength: function () {
    var c = 0;
    if (this.components && (this.components.length > 1)) {
      for (var b = 1, a = this.components.length; b < a; b++) {
        c += this.components[b - 1].distanceTo(this.components[b])
      }
    }
    return c
  },
  getGeodesicLength: function (b) {
    var e = this;
    if (b) {
      var c = new OpenLayers.Projection('EPSG:4326');
      if (!c.equals(b)) {
        e = this.clone().transform(b, c)
      }
    }
    var f = 0;
    if (e.components && (e.components.length > 1)) {
      var h,
      g;
      for (var d = 1, a = e.components.length; d < a; d++) {
        h = e.components[d - 1];
        g = e.components[d];
        f += OpenLayers.Util.distVincenty({
          lon: h.x,
          lat: h.y
        }, {
          lon: g.x,
          lat: g.y
        })
      }
    }
    return f * 1000
  },
  CLASS_NAME: 'OpenLayers.Geometry.Curve'
}); OpenLayers.Geometry.LineString = OpenLayers.Class(OpenLayers.Geometry.Curve, {
  removeComponent: function (a) {
    var b = this.components && (this.components.length > 2);
    if (b) {
      OpenLayers.Geometry.Collection.prototype.removeComponent.apply(this, arguments)
    }
    return b
  },
  intersects: function (m) {
    var c = false;
    var l = m.CLASS_NAME;
    if (l == 'OpenLayers.Geometry.LineString' || l == 'OpenLayers.Geometry.LinearRing' || l == 'OpenLayers.Geometry.Point') {
      var p = this.getSortedSegments();
      var n;
      if (l == 'OpenLayers.Geometry.Point') {
        n = [
          {
            x1: m.x,
            y1: m.y,
            x2: m.x,
            y2: m.y
          }
        ]
      } else {
        n = m.getSortedSegments()
      }
      var s,
      g,
      e,
      a,
      r,
      q,
      d,
      b;
      outer: for (var h = 0, k = p.length; h < k; ++h) {
        s = p[h];
        g = s.x1;
        e = s.x2;
        a = s.y1;
        r = s.y2;
        inner: for (var f = 0, o = n.length; f < o; ++f) {
          q = n[f];
          if (q.x1 > e) {
            break
          }
          if (q.x2 < g) {
            continue
          }
          d = q.y1;
          b = q.y2;
          if (Math.min(d, b) > Math.max(a, r)) {
            continue
          }
          if (Math.max(d, b) < Math.min(a, r)) {
            continue
          }
          if (OpenLayers.Geometry.segmentsIntersect(s, q)) {
            c = true;
            break outer
          }
        }
      }
    } else {
      c = m.intersects(this)
    }
    return c
  },
  getSortedSegments: function () {
    var a = this.components.length - 1;
    var b = new Array(a),
    e,
    d;
    for (var c = 0; c < a; ++c) {
      e = this.components[c];
      d = this.components[c + 1];
      if (e.x < d.x) {
        b[c] = {
          x1: e.x,
          y1: e.y,
          x2: d.x,
          y2: d.y
        }
      } else {
        b[c] = {
          x1: d.x,
          y1: d.y,
          x2: e.x,
          y2: e.y
        }
      }
    }
    function f(h, g) {
      return h.x1 - g.x1
    }
    return b.sort(f)
  },
  splitWithSegment: function (r, b) {
    var c = !(b && b.edge === false);
    var o = b && b.tolerance;
    var a = [
    ];
    var t = this.getVertices();
    var n = [
    ];
    var v = [
    ];
    var h = false;
    var e,
    d,
    l;
    var j,
    q,
    u;
    var f = {
      point: true,
      tolerance: o
    };
    var g = null;
    for (var m = 0, k = t.length - 2; m <= k; ++m) {
      e = t[m];
      n.push(e.clone());
      d = t[m + 1];
      u = {
        x1: e.x,
        y1: e.y,
        x2: d.x,
        y2: d.y
      };
      l = OpenLayers.Geometry.segmentsIntersect(r, u, f);
      if (l instanceof OpenLayers.Geometry.Point) {
        if ((l.x === r.x1 && l.y === r.y1) || (l.x === r.x2 && l.y === r.y2) || l.equals(e) || l.equals(d)) {
          q = true
        } else {
          q = false
        }
        if (q || c) {
          if (!l.equals(v[v.length - 1])) {
            v.push(l.clone())
          }
          if (m === 0) {
            if (l.equals(e)) {
              continue
            }
          }
          if (l.equals(d)) {
            continue
          }
          h = true;
          if (!l.equals(e)) {
            n.push(l)
          }
          a.push(new OpenLayers.Geometry.LineString(n));
          n = [
            l.clone()
          ]
        }
      }
    }
    if (h) {
      n.push(d.clone());
      a.push(new OpenLayers.Geometry.LineString(n))
    }
    if (v.length > 0) {
      var p = r.x1 < r.x2 ? 1 : - 1;
      var s = r.y1 < r.y2 ? 1 : - 1;
      g = {
        lines: a,
        points: v.sort(function (w, i) {
          return (p * w.x - p * i.x) || (s * w.y - s * i.y)
        })
      }
    }
    return g
  },
  split: function (x, b) {
    var n = null;
    var d = b && b.mutual;
    var l,
    e,
    m,
    c;
    if (x instanceof OpenLayers.Geometry.LineString) {
      var w = this.getVertices();
      var g,
      f,
      v,
      h,
      a,
      p;
      var s = [
      ];
      m = [
      ];
      for (var t = 0, o = w.length - 2; t <= o; ++t) {
        g = w[t];
        f = w[t + 1];
        v = {
          x1: g.x,
          y1: g.y,
          x2: f.x,
          y2: f.y
        };
        c = c || [x];
        if (d) {
          s.push(g.clone())
        }
        for (var r = 0; r < c.length;
        ++r) {
          h = c[r].splitWithSegment(v, b);
          if (h) {
            a = h.lines;
            if (a.length > 0) {
              a.unshift(r, 1);
              Array.prototype.splice.apply(c, a);
              r += a.length - 2
            }
            if (d) {
              for (var q = 0, u = h.points.length; q < u; ++q) {
                p = h.points[q];
                if (!p.equals(g)) {
                  s.push(p);
                  m.push(new OpenLayers.Geometry.LineString(s));
                  if (p.equals(f)) {
                    s = [
                    ]
                  } else {
                    s = [
                      p.clone()
                    ]
                  }
                }
              }
            }
          }
        }
      }
      if (d && m.length > 0 && s.length > 0) {
        s.push(f.clone());
        m.push(new OpenLayers.Geometry.LineString(s))
      }
    } else {
      n = x.splitWith(this, b)
    }
    if (c && c.length > 1) {
      e = true
    } else {
      c = [
      ]
    }
    if (m && m.length > 1) {
      l = true
    } else {
      m = [
      ]
    }
    if (e || l) {
      if (d) {
        n = [
          m,
          c
        ]
      } else {
        n = c
      }
    }
    return n
  },
  splitWith: function (b, a) {
    return b.split(this, a)
  },
  getVertices: function (a) {
    var b;
    if (a === true) {
      b = [
        this.components[0],
        this.components[this.components.length - 1]
      ]
    } else {
      if (a === false) {
        b = this.components.slice(1, this.components.length - 1)
      } else {
        b = this.components.slice()
      }
    }
    return b
  },
  distanceTo: function (h, g) {
    var k = !(g && g.edge === false);
    var B = k && g && g.details;
    var q,
    e = {
    };
    var t = Number.POSITIVE_INFINITY;
    if (h instanceof OpenLayers.Geometry.Point) {
      var r = this.getSortedSegments();
      var p = h.x;
      var o = h.y;
      var z;
      for (var v = 0, w = r.length; v < w; ++v) {
        z = r[v];
        q = OpenLayers.Geometry.distanceToSegment(h, z);
        if (q.distance < t) {
          t = q.distance;
          e = q;
          if (t === 0) {
            break
          }
        } else {
          if (z.x2 > p && ((o > z.y1 && o < z.y2) || (o < z.y1 && o > z.y2))) {
            break
          }
        }
      }
      if (B) {
        e = {
          distance: e.distance,
          x0: e.x,
          y0: e.y,
          x1: p,
          y1: o
        }
      } else {
        e = e.distance
      }
    } else {
      if (h instanceof OpenLayers.Geometry.LineString) {
        var d = this.getSortedSegments();
        var c = h.getSortedSegments();
        var b,
        a,
        n,
        A,
        f;
        var m = c.length;
        var l = {
          point: true
        };
        outer: for (var v = 0, w = d.length; v < w; ++v) {
          b = d[v];
          A = b.x1;
          f = b.y1;
          for (var u = 0; u < m; ++u) {
            a = c[u];
            n = OpenLayers.Geometry.segmentsIntersect(b, a, l);
            if (n) {
              t = 0;
              e = {
                distance: 0,
                x0: n.x,
                y0: n.y,
                x1: n.x,
                y1: n.y
              };
              break outer
            } else {
              q = OpenLayers.Geometry.distanceToSegment({
                x: A,
                y: f
              }, a);
              if (q.distance < t) {
                t = q.distance;
                e = {
                  distance: t,
                  x0: A,
                  y0: f,
                  x1: q.x,
                  y1: q.y
                }
              }
            }
          }
        }
        if (!B) {
          e = e.distance
        }
        if (t !== 0) {
          if (b) {
            q = h.distanceTo(new OpenLayers.Geometry.Point(b.x2, b.y2), g);
            var s = B ? q.distance : q;
            if (s < t) {
              if (B) {
                e = {
                  distance: t,
                  x0: q.x1,
                  y0: q.y1,
                  x1: q.x0,
                  y1: q.y0
                }
              } else {
                e = s
              }
            }
          }
        }
      } else {
        e = h.distanceTo(this, g);
        if (B) {
          e = {
            distance: e.distance,
            x0: e.x1,
            y0: e.y1,
            x1: e.x0,
            y1: e.y0
          }
        }
      }
    }
    return e
  },
  simplify: function (h) {
    if (this && this !== null) {
      var j = this.getVertices();
      if (j.length < 3) {
        return this
      }
      var d = function (l, k) {
        return (l - k)
      };
      var c = function (n, q, o, k) {
        var p = 0;
        var m = 0;
        for (var l = q, r; l < o; l++) {
          r = b(n[q], n[o], n[l]);
          if (r > p) {
            p = r;
            m = l
          }
        }
        if (p > k && m != q) {
          e.push(m);
          c(n, q, m, k);
          c(n, m, o, k)
        }
      };
      var b = function (o, n, l) {
        var p = Math.abs(0.5 * (o.x * n.y + n.x * l.y + l.x * o.y - n.x * o.y - l.x * n.y - o.x * l.y));
        var m = Math.sqrt(Math.pow(o.x - n.x, 2) + Math.pow(o.y - n.y, 2));
        var k = p / m * 2;
        return k
      };
      var f = 0;
      var i = j.length - 1;
      var e = [
      ];
      e.push(f);
      e.push(i);
      while (j[f].equals(j[i])) {
        i--;
        e.push(i)
      }
      c(j, f, i, h);
      var a = [
      ];
      e.sort(d);
      for (var g = 0; g < e.length; g++) {
        a.push(j[e[g]])
      }
      return new OpenLayers.Geometry.LineString(a)
    } else {
      return this
    }
  },
  CLASS_NAME: 'OpenLayers.Geometry.LineString'
}); OpenLayers.Geometry.LinearRing = OpenLayers.Class(OpenLayers.Geometry.LineString, {
  componentTypes: [
    'OpenLayers.Geometry.Point'
  ],
  addComponent: function (a, b) {
    var c = false;
    var d = this.components.pop();
    if (b != null || !a.equals(d)) {
      c = OpenLayers.Geometry.Collection.prototype.addComponent.apply(this, arguments)
    }
    var e = this.components[0];
    OpenLayers.Geometry.Collection.prototype.addComponent.apply(this, [
      e
    ]);
    return c
  },
  removeComponent: function (a) {
    var b = this.components && (this.components.length > 3);
    if (b) {
      this.components.pop();
      OpenLayers.Geometry.Collection.prototype.removeComponent.apply(this, arguments);
      var c = this.components[0];
      OpenLayers.Geometry.Collection.prototype.addComponent.apply(this, [
        c
      ])
    }
    return b
  },
  move: function (b, d) {
    for (var c = 0, a = this.components.length; c < a - 1; c++) {
      this.components[c].move(b, d)
    }
  },
  rotate: function (d, b) {
    for (var c = 0, a = this.components.length; c < a - 1; ++c) {
      this.components[c].rotate(d, b)
    }
  },
  resize: function (e, b, d) {
    for (var c = 0, a = this.components.length;
    c < a - 1; ++c) {
      this.components[c].resize(e, b, d)
    }
    return this
  },
  transform: function (e, c) {
    if (e && c) {
      for (var d = 0, a = this.components.length; d < a - 1; d++) {
        var b = this.components[d];
        b.transform(e, c)
      }
      this.bounds = null
    }
    return this
  },
  getCentroid: function () {
    if (this.components) {
      var f = this.components.length;
      if (f > 0 && f <= 2) {
        return this.components[0].clone()
      } else {
        if (f > 2) {
          var j = 0;
          var g = 0;
          var d = this.components[0].x;
          var n = this.components[0].y;
          var a = - 1 * this.getArea();
          if (a != 0) {
            for (var e = 0; e < f - 1; e++) {
              var l = this.components[e];
              var h = this.components[e + 1];
              j += (l.x + h.x - 2 * d) * ((l.x - d) * (h.y - n) - (h.x - d) * (l.y - n));
              g += (l.y + h.y - 2 * n) * ((l.x - d) * (h.y - n) - (h.x - d) * (l.y - n))
            }
            var m = d + j / (6 * a);
            var k = n + g / (6 * a)
          } else {
            for (var e = 0; e < f - 1; e++) {
              j += this.components[e].x;
              g += this.components[e].y
            }
            var m = j / (f - 1);
            var k = g / (f - 1)
          }
          return new OpenLayers.Geometry.Point(m, k)
        } else {
          return null
        }
      }
    }
  },
  getArea: function () {
    var g = 0;
    if (this.components && (this.components.length > 2)) {
      var f = 0;
      for (var e = 0, d = this.components.length;
      e < d - 1; e++) {
        var a = this.components[e];
        var h = this.components[e + 1];
        f += (a.x + h.x) * (h.y - a.y)
      }
      g = - f / 2
    }
    return g
  },
  getGeodesicArea: function (b) {
    var d = this;
    if (b) {
      var c = new OpenLayers.Projection('EPSG:4326');
      if (!c.equals(b)) {
        d = this.clone().transform(b, c)
      }
    }
    var f = 0;
    var a = d.components && d.components.length;
    if (a > 2) {
      var h,
      g;
      for (var e = 0; e < a - 1; e++) {
        h = d.components[e];
        g = d.components[e + 1];
        f += OpenLayers.Util.rad(g.x - h.x) * (2 + Math.sin(OpenLayers.Util.rad(h.y)) + Math.sin(OpenLayers.Util.rad(g.y)))
      }
      f = f * 6378137 * 6378137 / 2
    }
    return f
  },
  containsPoint: function (m) {
    var s = OpenLayers.Number.limitSigDigs;
    var l = 14;
    var k = s(m.x, l);
    var j = s(m.y, l);
    function r(w, t, v, i, u) {
      return (w - u) * ((i - t) / (u - v)) + i
    }
    var a = this.components.length - 1;
    var g,
    f,
    q,
    d,
    o,
    b,
    e,
    c;
    var h = 0;
    for (var n = 0; n < a; ++n) {
      g = this.components[n];
      q = s(g.x, l);
      d = s(g.y, l);
      f = this.components[n + 1];
      o = s(f.x, l);
      b = s(f.y, l);
      if (d == b) {
        if (j == d) {
          if (q <= o && (k >= q && k <= o) || q >= o && (k <= q && k >= o)) {
            h = - 1;
            break
          }
        }
        continue
      }
      e = s(r(j, q, d, o, b), l);
      if (e == k) {
        if (d < b && (j >= d && j <= b) || d > b && (j <= d && j >= b)) {
          h = - 1;
          break
        }
      }
      if (e <= k) {
        continue
      }
      if (q != o && (e < Math.min(q, o) || e > Math.max(q, o))) {
        continue
      }
      if (d < b && (j >= d && j < b) || d > b && (j < d && j >= b)) {
        ++h
      }
    }
    var p = (h == - 1) ? 1 : !!(h & 1);
    return p
  },
  intersects: function (d) {
    var b = false;
    if (d.CLASS_NAME == 'OpenLayers.Geometry.Point') {
      b = this.containsPoint(d)
    } else {
      if (d.CLASS_NAME == 'OpenLayers.Geometry.LineString') {
        b = d.intersects(this)
      } else {
        if (d.CLASS_NAME == 'OpenLayers.Geometry.LinearRing') {
          b = OpenLayers.Geometry.LineString.prototype.intersects.apply(this, [
            d
          ])
        } else {
          for (var c = 0, a = d.components.length; c < a; ++c) {
            b = d.components[c].intersects(this);
            if (b) {
              break
            }
          }
        }
      }
    }
    return b
  },
  getVertices: function (a) {
    return (a === true) ? [
    ] : this.components.slice(0, this.components.length - 1)
  },
  CLASS_NAME: 'OpenLayers.Geometry.LinearRing'
}); OpenLayers.Geometry.Polygon = OpenLayers.Class(OpenLayers.Geometry.Collection, {
  componentTypes: [
    'OpenLayers.Geometry.LinearRing'
  ],
  getArea: function () {
    var c = 0;
    if (this.components && (this.components.length > 0)) {
      c += Math.abs(this.components[0].getArea());
      for (var b = 1, a = this.components.length; b < a; b++) {
        c -= Math.abs(this.components[b].getArea())
      }
    }
    return c
  },
  getGeodesicArea: function (b) {
    var d = 0;
    if (this.components && (this.components.length > 0)) {
      d += Math.abs(this.components[0].getGeodesicArea(b));
      for (var c = 1, a = this.components.length; c < a; c++) {
        d -= Math.abs(this.components[c].getGeodesicArea(b))
      }
    }
    return d
  },
  containsPoint: function (a) {
    var e = this.components.length;
    var c = false;
    if (e > 0) {
      c = this.components[0].containsPoint(a);
      if (c !== 1) {
        if (c && e > 1) {
          var d;
          for (var b = 1; b < e; ++b) {
            d = this.components[b].containsPoint(a);
            if (d) {
              if (d === 1) {
                c = 1
              } else {
                c = false
              }
              break
            }
          }
        }
      }
    }
    return c
  },
  intersects: function (e) {
    var b = false;
    var d,
    a;
    if (e.CLASS_NAME == 'OpenLayers.Geometry.Point') {
      b = this.containsPoint(e)
    } else {
      if (e.CLASS_NAME == 'OpenLayers.Geometry.LineString' || e.CLASS_NAME == 'OpenLayers.Geometry.LinearRing') {
        for (d = 0, a = this.components.length; d < a; ++d) {
          b = e.intersects(this.components[d]);
          if (b) {
            break
          }
        }
        if (!b) {
          for (d = 0, a = e.components.length;
          d < a; ++d) {
            b = this.containsPoint(e.components[d]);
            if (b) {
              break
            }
          }
        }
      } else {
        for (d = 0, a = e.components.length; d < a; ++d) {
          b = this.intersects(e.components[d]);
          if (b) {
            break
          }
        }
      }
    }
    if (!b && e.CLASS_NAME == 'OpenLayers.Geometry.Polygon') {
      var c = this.components[0];
      for (d = 0, a = c.components.length; d < a; ++d) {
        b = e.containsPoint(c.components[d]);
        if (b) {
          break
        }
      }
    }
    return b
  },
  distanceTo: function (d, b) {
    var c = !(b && b.edge === false);
    var a;
    if (!c && this.intersects(d)) {
      a = 0
    } else {
      a = OpenLayers.Geometry.Collection.prototype.distanceTo.apply(this, [
        d,
        b
      ])
    }
    return a
  },
  CLASS_NAME: 'OpenLayers.Geometry.Polygon'
}); OpenLayers.Geometry.Polygon.createRegularPolygon = function (j, f, b, l) {
  var c = Math.PI * ((1 / b) - (1 / 2));
  if (l) {
    c += (l / 180) * Math.PI
  }
  var a,
  h,
  g;
  var k = [
  ];
  for (var e = 0; e < b; ++e) {
    a = c + (e * 2 * Math.PI / b);
    h = j.x + (f * Math.cos(a));
    g = j.y + (f * Math.sin(a));
    k.push(new OpenLayers.Geometry.Point(h, g))
  }
  var d = new OpenLayers.Geometry.LinearRing(k);
  return new OpenLayers.Geometry.Polygon([d])
}; OpenLayers.Geometry.MultiPolygon = OpenLayers.Class(OpenLayers.Geometry.Collection, {
  componentTypes: [
    'OpenLayers.Geometry.Polygon'
  ],
  CLASS_NAME: 'OpenLayers.Geometry.MultiPolygon'
});
OpenLayers.Feature = OpenLayers.Class({
  layer: null,
  id: null,
  lonlat: null,
  data: null,
  marker: null,
  popupClass: null,
  popup: null,
  initialize: function (a, c, b) {
    this.layer = a;
    this.lonlat = c;
    this.data = (b != null) ? b : {
    };
    this.id = OpenLayers.Util.createUniqueID(this.CLASS_NAME + '_')
  },
  destroy: function () {
    if ((this.layer != null) && (this.layer.map != null)) {
      if (this.popup != null) {
        this.layer.map.removePopup(this.popup)
      }
    }
    if (this.layer != null && this.marker != null) {
      this.layer.removeMarker(this.marker)
    }
    this.layer = null;
    this.id = null;
    this.lonlat = null;
    this.data = null;
    if (this.marker != null) {
      this.destroyMarker(this.marker);
      this.marker = null
    }
    if (this.popup != null) {
      this.destroyPopup(this.popup);
      this.popup = null
    }
  },
  onScreen: function () {
    var b = false;
    if ((this.layer != null) && (this.layer.map != null)) {
      var a = this.layer.map.getExtent();
      b = a.containsLonLat(this.lonlat)
    }
    return b
  },
  createMarker: function () {
    if (this.lonlat != null) {
      this.marker = new OpenLayers.Marker(this.lonlat, this.data.icon)
    }
    return this.marker
  },
  destroyMarker: function () {
    this.marker.destroy()
  },
  createPopup: function (b) {
    if (this.lonlat != null) {
      if (!this.popup) {
        var a = (this.marker) ? this.marker.icon : null;
        var c = this.popupClass ? this.popupClass : OpenLayers.Popup.Anchored;
        this.popup = new c(this.id + '_popup', this.lonlat, this.data.popupSize, this.data.popupContentHTML, a, b)
      }
      if (this.data.overflow != null) {
        this.popup.contentDiv.style.overflow = this.data.overflow
      }
      this.popup.feature = this
    }
    return this.popup
  },
  destroyPopup: function () {
    if (this.popup) {
      this.popup.feature = null;
      this.popup.destroy();
      this.popup = null
    }
  },
  CLASS_NAME: 'OpenLayers.Feature'
}); OpenLayers.State = {
  UNKNOWN: 'Unknown',
  INSERT: 'Insert',
  UPDATE: 'Update',
  DELETE: 'Delete'
}; OpenLayers.Feature.Vector = OpenLayers.Class(OpenLayers.Feature, {
  fid: null,
  geometry: null,
  attributes: null,
  bounds: null,
  state: null,
  style: null,
  url: null,
  renderIntent: 'default',
  modified: null,
  initialize: function (c, a, b) {
    OpenLayers.Feature.prototype.initialize.apply(this, [
      null,
      null,
      a
    ]);
    this.lonlat = null;
    this.geometry = c ? c : null;
    this.state = null;
    this.attributes = {
    };
    if (a) {
      this.attributes = OpenLayers.Util.extend(this.attributes, a)
    }
    this.style = b ? b : null
  },
  destroy: function () {
    if (this.layer) {
      this.layer.removeFeatures(this);
      this.layer = null
    }
    this.geometry = null;
    this.modified = null;
    OpenLayers.Feature.prototype.destroy.apply(this, arguments)
  },
  clone: function () {
    return new OpenLayers.Feature.Vector(this.geometry ? this.geometry.clone()  : null, this.attributes, this.style)
  },
  onScreen: function (d) {
    var c = false;
    if (this.layer && this.layer.map) {
      var a = this.layer.map.getExtent();
      if (d) {
        var b = this.geometry.getBounds();
        c = a.intersectsBounds(b)
      } else {
        var e = a.toGeometry();
        c = e.intersects(this.geometry)
      }
    }
    return c
  },
  getVisibility: function () {
    return !(this.style && this.style.display == 'none' || !this.layer || this.layer && this.layer.styleMap && this.layer.styleMap.createSymbolizer(this, this.renderIntent).display == 'none' || this.layer && !this.layer.getVisibility())
  },
  createMarker: function () {
    return null
  },
  destroyMarker: function () {
  },
  createPopup: function () {
    return null
  },
  atPoint: function (b, d, c) {
    var a = false;
    if (this.geometry) {
      a = this.geometry.atPoint(b, d, c)
    }
    return a
  },
  destroyPopup: function () {
  },
  move: function (a) {
    if (!this.layer || !this.geometry.move) {
      return undefined
    }
    var b;
    if (a.CLASS_NAME == 'OpenLayers.LonLat') {
      b = this.layer.getViewPortPxFromLonLat(a)
    } else {
      b = a
    }
    var d = this.layer.getViewPortPxFromLonLat(this.geometry.getBounds().getCenterLonLat());
    var c = this.layer.map.getResolution();
    this.geometry.move(c * (b.x - d.x), c * (d.y - b.y));
    this.layer.drawFeature(this);
    return d
  },
  toState: function (a) {
    if (a == OpenLayers.State.UPDATE) {
      switch (this.state) {
        case OpenLayers.State.UNKNOWN:
        case OpenLayers.State.DELETE:
          this.state = a;
          break;
        case OpenLayers.State.UPDATE:
        case OpenLayers.State.INSERT:
          break
      }
    } else {
      if (a == OpenLayers.State.INSERT) {
        switch (this.state) {
          case OpenLayers.State.UNKNOWN:
            break;
          default:
            this.state = a;
            break
        }
      } else {
        if (a == OpenLayers.State.DELETE) {
          switch (this.state) {
            case OpenLayers.State.INSERT:
              break;
            case OpenLayers.State.DELETE:
              break;
            case OpenLayers.State.UNKNOWN:
            case OpenLayers.State.UPDATE:
              this.state = a;
              break
          }
        } else {
          if (a == OpenLayers.State.UNKNOWN) {
            this.state = a
          }
        }
      }
    }
  },
  CLASS_NAME: 'OpenLayers.Feature.Vector'
});
OpenLayers.Feature.Vector.style = {
  'default': {
    fillColor: '#ee9900',
    fillOpacity: 0.4,
    hoverFillColor: 'white',
    hoverFillOpacity: 0.8,
    strokeColor: '#ee9900',
    strokeOpacity: 1,
    strokeWidth: 1,
    strokeLinecap: 'round',
    strokeDashstyle: 'solid',
    hoverStrokeColor: 'red',
    hoverStrokeOpacity: 1,
    hoverStrokeWidth: 0.2,
    pointRadius: 6,
    hoverPointRadius: 1,
    hoverPointUnit: '%',
    pointerEvents: 'visiblePainted',
    cursor: 'inherit',
    fontColor: '#000000',
    labelAlign: 'cm',
    labelOutlineColor: 'white',
    labelOutlineWidth: 3
  },
  select: {
    fillColor: 'blue',
    fillOpacity: 0.4,
    hoverFillColor: 'white',
    hoverFillOpacity: 0.8,
    strokeColor: 'blue',
    strokeOpacity: 1,
    strokeWidth: 2,
    strokeLinecap: 'round',
    strokeDashstyle: 'solid',
    hoverStrokeColor: 'red',
    hoverStrokeOpacity: 1,
    hoverStrokeWidth: 0.2,
    pointRadius: 6,
    hoverPointRadius: 1,
    hoverPointUnit: '%',
    pointerEvents: 'visiblePainted',
    cursor: 'pointer',
    fontColor: '#000000',
    labelAlign: 'cm',
    labelOutlineColor: 'white',
    labelOutlineWidth: 3
  },
  temporary: {
    fillColor: '#66cccc',
    fillOpacity: 0.2,
    hoverFillColor: 'white',
    hoverFillOpacity: 0.8,
    strokeColor: '#66cccc',
    strokeOpacity: 1,
    strokeLinecap: 'round',
    strokeWidth: 2,
    strokeDashstyle: 'solid',
    hoverStrokeColor: 'red',
    hoverStrokeOpacity: 1,
    hoverStrokeWidth: 0.2,
    pointRadius: 6,
    hoverPointRadius: 1,
    hoverPointUnit: '%',
    pointerEvents: 'visiblePainted',
    cursor: 'inherit',
    fontColor: '#000000',
    labelAlign: 'cm',
    labelOutlineColor: 'white',
    labelOutlineWidth: 3
  },
  'delete': {
    display: 'none'
  }
};
OpenLayers.Style = OpenLayers.Class({
  id: null,
  name: null,
  title: null,
  description: null,
  layerName: null,
  isDefault: false,
  rules: null,
  context: null,
  defaultStyle: null,
  defaultsPerSymbolizer: false,
  propertyStyles: null,
  initialize: function (b, a) {
    OpenLayers.Util.extend(this, a);
    this.rules = [
    ];
    if (a && a.rules) {
      this.addRules(a.rules)
    }
    this.setDefaultStyle(b || OpenLayers.Feature.Vector.style['default']);
    this.id = OpenLayers.Util.createUniqueID(this.CLASS_NAME + '_')
  },
  destroy: function () {
    for (var b = 0, a = this.rules.length; b < a; b++) {
      this.rules[b].destroy();
      this.rules[b] = null
    }
    this.rules = null;
    this.defaultStyle = null
  },
  createSymbolizer: function (k) {
    var a = this.defaultsPerSymbolizer ? {
    }
     : this.createLiterals(OpenLayers.Util.extend({
    }, this.defaultStyle), k);
    var j = this.rules;
    var h,
    b;
    var c = [
    ];
    var f = false;
    for (var d = 0, e = j.length; d < e; d++) {
      h = j[d];
      var g = h.evaluate(k);
      if (g) {
        if (h instanceof OpenLayers.Rule && h.elseFilter) {
          c.push(h)
        } else {
          f = true;
          this.applySymbolizer(h, a, k)
        }
      }
    }
    if (f == false && c.length > 0) {
      f = true;
      for (var d = 0, e = c.length; d < e; d++) {
        this.applySymbolizer(c[d], a, k)
      }
    }
    if (j.length > 0 && f == false) {
      a.display = 'none'
    }
    if (a.label != null && typeof a.label !== 'string') {
      a.label = String(a.label)
    }
    return a
  },
  applySymbolizer: function (f, d, b) {
    var a = b.geometry ? this.getSymbolizerPrefix(b.geometry)  : OpenLayers.Style.SYMBOLIZER_PREFIXES[0];
    var c = f.symbolizer[a] || f.symbolizer;
    if (this.defaultsPerSymbolizer === true) {
      var e = this.defaultStyle;
      OpenLayers.Util.applyDefaults(c, {
        pointRadius: e.pointRadius
      });
      if (c.stroke === true || c.graphic === true) {
        OpenLayers.Util.applyDefaults(c, {
          strokeWidth: e.strokeWidth,
          strokeColor: e.strokeColor,
          strokeOpacity: e.strokeOpacity,
          strokeDashstyle: e.strokeDashstyle,
          strokeLinecap: e.strokeLinecap
        })
      }
      if (c.fill === true || c.graphic === true) {
        OpenLayers.Util.applyDefaults(c, {
          fillColor: e.fillColor,
          fillOpacity: e.fillOpacity
        })
      }
      if (c.graphic === true) {
        OpenLayers.Util.applyDefaults(c, {
          pointRadius: this.defaultStyle.pointRadius,
          externalGraphic: this.defaultStyle.externalGraphic,
          graphicName: this.defaultStyle.graphicName,
          graphicOpacity: this.defaultStyle.graphicOpacity,
          graphicWidth: this.defaultStyle.graphicWidth,
          graphicHeight: this.defaultStyle.graphicHeight,
          graphicXOffset: this.defaultStyle.graphicXOffset,
          graphicYOffset: this.defaultStyle.graphicYOffset
        })
      }
    }
    return this.createLiterals(OpenLayers.Util.extend(d, c), b)
  },
  createLiterals: function (d, c) {
    var b = OpenLayers.Util.extend({
    }, c.attributes || c.data);
    OpenLayers.Util.extend(b, this.context);
    for (var a in this.propertyStyles) {
      d[a] = OpenLayers.Style.createLiteral(d[a], b, c, a)
    }
    return d
  },
  findPropertyStyles: function () {
    var d = {
    };
    var f = this.defaultStyle;
    this.addPropertyStyles(d, f);
    var h = this.rules;
    var e,
    g;
    for (var c = 0, a = h.length; c < a; c++) {
      e = h[c].symbolizer;
      for (var b in e) {
        g = e[b];
        if (typeof g == 'object') {
          this.addPropertyStyles(d, g)
        } else {
          this.addPropertyStyles(d, e);
          break
        }
      }
    }
    return d
  },
  addPropertyStyles: function (b, c) {
    var d;
    for (var a in c) {
      d = c[a];
      if (typeof d == 'string' && d.match(/\$\{\w+\}/)) {
        b[a] = true
      }
    }
    return b
  },
  addRules: function (a) {
    Array.prototype.push.apply(this.rules, a);
    this.propertyStyles = this.findPropertyStyles()
  },
  setDefaultStyle: function (a) {
    this.defaultStyle = a;
    this.propertyStyles = this.findPropertyStyles()
  },
  getSymbolizerPrefix: function (d) {
    var c = OpenLayers.Style.SYMBOLIZER_PREFIXES;
    for (var b = 0, a = c.length; b < a; b++) {
      if (d.CLASS_NAME.indexOf(c[b]) != - 1) {
        return c[b]
      }
    }
  },
  clone: function () {
    var b = OpenLayers.Util.extend({
    }, this);
    if (this.rules) {
      b.rules = [
      ];
      for (var c = 0, a = this.rules.length; c < a; ++c) {
        b.rules.push(this.rules[c].clone())
      }
    }
    b.context = this.context && OpenLayers.Util.extend({
    }, this.context);
    var d = OpenLayers.Util.extend({
    }, this.defaultStyle);
    return new OpenLayers.Style(d, b)
  },
  CLASS_NAME: 'OpenLayers.Style'
});
OpenLayers.Style.createLiteral = function (d, b, a, c) {
  if (typeof d == 'string' && d.indexOf('${') != - 1) {
    d = OpenLayers.String.format(d, b, [
      a,
      c
    ]);
    d = (isNaN(d) || !d) ? d : parseFloat(d)
  }
  return d
};
OpenLayers.Style.SYMBOLIZER_PREFIXES = [
  'Point',
  'Line',
  'Polygon',
  'Text',
  'Raster'
];
OpenLayers.StyleMap = OpenLayers.Class({
  styles: null,
  extendDefault: true,
  initialize: function (c, a) {
    this.styles = {
      'default': new OpenLayers.Style(OpenLayers.Feature.Vector.style['default']),
      select: new OpenLayers.Style(OpenLayers.Feature.Vector.style.select),
      temporary: new OpenLayers.Style(OpenLayers.Feature.Vector.style.temporary),
      'delete': new OpenLayers.Style(OpenLayers.Feature.Vector.style['delete'])
    };
    if (c instanceof OpenLayers.Style) {
      this.styles['default'] = c;
      this.styles.select = c;
      this.styles.temporary = c;
      this.styles['delete'] = c
    } else {
      if (typeof c == 'object') {
        for (var b in c) {
          if (c[b] instanceof OpenLayers.Style) {
            this.styles[b] = c[b]
          } else {
            if (typeof c[b] == 'object') {
              this.styles[b] = new OpenLayers.Style(c[b])
            } else {
              this.styles['default'] = new OpenLayers.Style(c);
              this.styles.select = new OpenLayers.Style(c);
              this.styles.temporary = new OpenLayers.Style(c);
              this.styles['delete'] = new OpenLayers.Style(c);
              break
            }
          }
        }
      }
    }
    OpenLayers.Util.extend(this, a)
  },
  destroy: function () {
    for (var a in this.styles) {
      this.styles[a].destroy()
    }
    this.styles = null
  },
  createSymbolizer: function (b, c) {
    if (!b) {
      b = new OpenLayers.Feature.Vector()
    }
    if (!this.styles[c]) {
      c = 'default'
    }
    b.renderIntent = c;
    var a = {
    };
    if (this.extendDefault && c != 'default') {
      a = this.styles['default'].createSymbolizer(b)
    }
    return OpenLayers.Util.extend(a, this.styles[c].createSymbolizer(b))
  },
  addUniqueValueRules: function (b, d, f, a) {
    var e = [
    ];
    for (var c in f) {
      e.push(new OpenLayers.Rule({
        symbolizer: f[c],
        context: a,
        filter: new OpenLayers.Filter.Comparison({
          type: OpenLayers.Filter.Comparison.EQUAL_TO,
          property: d,
          value: c
        })
      }))
    }
    this.styles[b].addRules(e)
  },
  CLASS_NAME: 'OpenLayers.StyleMap'
});
OpenLayers.Renderer.Canvas = OpenLayers.Class(OpenLayers.Renderer, {
  hitDetection: true,
  hitOverflow: 0,
  canvas: null,
  features: null,
  pendingRedraw: false,
  cachedSymbolBounds: {
  },
  initialize: function (a, b) {
    OpenLayers.Renderer.prototype.initialize.apply(this, arguments);
    this.root = document.createElement('canvas');
    this.container.appendChild(this.root);
    this.canvas = this.root.getContext('2d');
    this.features = {
    };
    if (this.hitDetection) {
      this.hitCanvas = document.createElement('canvas');
      this.hitContext = this.hitCanvas.getContext('2d')
    }
  },
  setExtent: function () {
    OpenLayers.Renderer.prototype.setExtent.apply(this, arguments);
    return false
  },
  eraseGeometry: function (b, a) {
    this.eraseFeatures(this.features[a][0])
  },
  supported: function () {
    return OpenLayers.CANVAS_SUPPORTED
  },
  setSize: function (b) {
    this.size = b.clone();
    var a = this.root;
    a.style.width = b.w + 'px';
    a.style.height = b.h + 'px';
    a.width = b.w;
    a.height = b.h;
    this.resolution = null;
    if (this.hitDetection) {
      var c = this.hitCanvas;
      c.style.width = b.w + 'px';
      c.style.height = b.h + 'px';
      c.width = b.w;
      c.height = b.h
    }
  },
  drawFeature: function (a, b) {
    var f;
    if (a.geometry) {
      b = this.applyDefaultSymbolizer(b || a.style);
      var e = a.geometry.getBounds();
      var d;
      if (this.map.baseLayer && this.map.baseLayer.wrapDateLine) {
        d = this.map.getMaxExtent()
      }
      var c = e && e.intersectsBounds(this.extent, {
        worldBounds: d
      });
      f = (b.display !== 'none') && !!e && c;
      if (f) {
        this.features[a.id] = [
          a,
          b
        ]
      } else {
        delete (this.features[a.id])
      }
      this.pendingRedraw = true
    }
    if (this.pendingRedraw && !this.locked) {
      this.redraw();
      this.pendingRedraw = false
    }
    return f
  },
  drawGeometry: function (e, c, d) {
    var b = e.CLASS_NAME;
    if ((b == 'OpenLayers.Geometry.Collection') || (b == 'OpenLayers.Geometry.MultiPoint') || (b == 'OpenLayers.Geometry.MultiLineString') || (b == 'OpenLayers.Geometry.MultiPolygon')) {
      for (var a = 0;
      a < e.components.length; a++) {
        this.drawGeometry(e.components[a], c, d)
      }
      return
    }
    switch (e.CLASS_NAME) {
      case 'OpenLayers.Geometry.Point':
        this.drawPoint(e, c, d);
        break;
      case 'OpenLayers.Geometry.LineString':
        this.drawLineString(e, c, d);
        break;
      case 'OpenLayers.Geometry.LinearRing':
        this.drawLinearRing(e, c, d);
        break;
      case 'OpenLayers.Geometry.Polygon':
        this.drawPolygon(e, c, d);
        break;
      default:
        break
    }
  },
  drawExternalGraphic: function (i, a, d) {
    var e = new Image();
    var j = a.title || a.graphicTitle;
    if (j) {
      e.title = j
    }
    var b = a.graphicWidth || a.graphicHeight;
    var k = a.graphicHeight || a.graphicWidth;
    b = b ? b : a.pointRadius * 2;
    k = k ? k : a.pointRadius * 2;
    var g = (a.graphicXOffset != undefined) ? a.graphicXOffset : - (0.5 * b);
    var c = (a.graphicYOffset != undefined) ? a.graphicYOffset : - (0.5 * k);
    var f = a.graphicOpacity || a.fillOpacity;
    var h = function () {
      if (!this.features[d]) {
        return
      }
      var o = this.getLocalXY(i);
      var r = o[0];
      var p = o[1];
      if (!isNaN(r) && !isNaN(p)) {
        var l = (r + g) | 0;
        var q = (p + c) | 0;
        var m = this.canvas;
        m.globalAlpha = f;
        var n = OpenLayers.Renderer.Canvas.drawImageScaleFactor || (OpenLayers.Renderer.Canvas.drawImageScaleFactor = /android 2.1/.test(navigator.userAgent.toLowerCase()) ? 320 / window.screen.width : 1);
        m.drawImage(e, l * n, q * n, b * n, k * n);
        if (this.hitDetection) {
          this.setHitContextStyle('fill', d);
          this.hitContext.fillRect(l, q, b, k)
        }
      }
    };
    e.onload = OpenLayers.Function.bind(h, this);
    e.src = a.externalGraphic
  },
  drawNamedSymbol: function (m, b, h) {
    var n,
    l,
    g,
    f,
    j,
    a,
    c,
    e;
    var k;
    var o = Math.PI / 180;
    var d = OpenLayers.Renderer.symbol[b.graphicName];
    if (!d) {
      throw new Error(b.graphicName + ' is not a valid symbol name')
    }
    if (!d.length || d.length < 2) {
      return
    }
    var r = this.getLocalXY(m);
    var q = r[0];
    var p = r[1];
    if (isNaN(q) || isNaN(p)) {
      return
    }
    this.canvas.lineCap = 'round';
    this.canvas.lineJoin = 'round';
    if (this.hitDetection) {
      this.hitContext.lineCap = 'round';
      this.hitContext.lineJoin = 'round'
    }
    if (b.graphicName in this.cachedSymbolBounds) {
      a = this.cachedSymbolBounds[b.graphicName]
    } else {
      a = new OpenLayers.Bounds();
      for (j = 0; j < d.length; j += 2) {
        a.extend(new OpenLayers.LonLat(d[j], d[j + 1]))
      }
      this.cachedSymbolBounds[b.graphicName] = a
    }
    this.canvas.save();
    if (this.hitDetection) {
      this.hitContext.save()
    }
    this.canvas.translate(q, p);
    if (this.hitDetection) {
      this.hitContext.translate(q, p)
    }
    e = o * b.rotation;
    if (!isNaN(e)) {
      this.canvas.rotate(e);
      if (this.hitDetection) {
        this.hitContext.rotate(e)
      }
    }
    c = 2 * b.pointRadius / Math.max(a.getWidth(), a.getHeight());
    this.canvas.scale(c, c);
    if (this.hitDetection) {
      this.hitContext.scale(c, c)
    }
    g = a.getCenterLonLat().lon;
    f = a.getCenterLonLat().lat;
    this.canvas.translate( - g, - f);
    if (this.hitDetection) {
      this.hitContext.translate( - g, - f)
    }
    k = b.strokeWidth;
    b.strokeWidth = k / c;
    if (b.fill !== false) {
      this.setCanvasStyle('fill', b);
      this.canvas.beginPath();
      for (j = 0; j < d.length; j = j + 2) {
        n = d[j];
        l = d[j + 1];
        if (j == 0) {
          this.canvas.moveTo(n, l)
        }
        this.canvas.lineTo(n, l)
      }
      this.canvas.closePath();
      this.canvas.fill();
      if (this.hitDetection) {
        this.setHitContextStyle('fill', h, b);
        this.hitContext.beginPath();
        for (j = 0; j < d.length; j = j + 2) {
          n = d[j];
          l = d[j + 1];
          if (j == 0) {
            this.canvas.moveTo(n, l)
          }
          this.hitContext.lineTo(n, l)
        }
        this.hitContext.closePath();
        this.hitContext.fill()
      }
    }
    if (b.stroke !== false) {
      this.setCanvasStyle('stroke', b);
      this.canvas.beginPath();
      for (j = 0; j < d.length; j = j + 2) {
        n = d[j];
        l = d[j + 1];
        if (j == 0) {
          this.canvas.moveTo(n, l)
        }
        this.canvas.lineTo(n, l)
      }
      this.canvas.closePath();
      this.canvas.stroke();
      if (this.hitDetection) {
        this.setHitContextStyle('stroke', h, b, c);
        this.hitContext.beginPath();
        for (j = 0; j < d.length; j = j + 2) {
          n = d[j];
          l = d[j + 1];
          if (j == 0) {
            this.hitContext.moveTo(n, l)
          }
          this.hitContext.lineTo(n, l)
        }
        this.hitContext.closePath();
        this.hitContext.stroke()
      }
    }
    b.strokeWidth = k;
    this.canvas.restore();
    if (this.hitDetection) {
      this.hitContext.restore()
    }
    this.setCanvasStyle('reset')
  },
  setCanvasStyle: function (b, a) {
    if (b === 'fill') {
      this.canvas.globalAlpha = a.fillOpacity;
      this.canvas.fillStyle = a.fillColor
    } else {
      if (b === 'stroke') {
        this.canvas.globalAlpha = a.strokeOpacity;
        this.canvas.strokeStyle = a.strokeColor;
        this.canvas.lineWidth = a.strokeWidth
      } else {
        this.canvas.globalAlpha = 0;
        this.canvas.lineWidth = 1
      }
    }
  },
  featureIdToHex: function (c) {
    var d = Number(c.split('_').pop()) + 1;
    if (d >= 16777216) {
      this.hitOverflow = d - 16777215;
      d = d % 16777216 + 1
    }
    var b = '000000' + d.toString(16);
    var a = b.length;
    b = '#' + b.substring(a - 6, a);
    return b
  },
  setHitContextStyle: function (b, e, a, d) {
    var c = this.featureIdToHex(e);
    if (b == 'fill') {
      this.hitContext.globalAlpha = 1;
      this.hitContext.fillStyle = c
    } else {
      if (b == 'stroke') {
        this.hitContext.globalAlpha = 1;
        this.hitContext.strokeStyle = c;
        if (typeof d === 'undefined') {
          this.hitContext.lineWidth = a.strokeWidth + 2
        } else {
          if (!isNaN(d)) {
            this.hitContext.lineWidth = a.strokeWidth + 2 / d
          }
        }
      } else {
        this.hitContext.globalAlpha = 0;
        this.hitContext.lineWidth = 1
      }
    }
  },
  drawPoint: function (g, c, f) {
    if (c.graphic !== false) {
      if (c.externalGraphic) {
        this.drawExternalGraphic(g, c, f)
      } else {
        if (c.graphicName && (c.graphicName != 'circle')) {
          this.drawNamedSymbol(g, c, f)
        } else {
          var d = this.getLocalXY(g);
          var h = d[0];
          var e = d[1];
          if (!isNaN(h) && !isNaN(e)) {
            var b = Math.PI * 2;
            var a = c.pointRadius;
            if (c.fill !== false) {
              this.setCanvasStyle('fill', c);
              this.canvas.beginPath();
              this.canvas.arc(h, e, a, 0, b, true);
              this.canvas.fill();
              if (this.hitDetection) {
                this.setHitContextStyle('fill', f, c);
                this.hitContext.beginPath();
                this.hitContext.arc(h, e, a, 0, b, true);
                this.hitContext.fill()
              }
            }
            if (c.stroke !== false) {
              this.setCanvasStyle('stroke', c);
              this.canvas.beginPath();
              this.canvas.arc(h, e, a, 0, b, true);
              this.canvas.stroke();
              if (this.hitDetection) {
                this.setHitContextStyle('stroke', f, c);
                this.hitContext.beginPath();
                this.hitContext.arc(h, e, a, 0, b, true);
                this.hitContext.stroke()
              }
              this.setCanvasStyle('reset')
            }
          }
        }
      }
    }
  },
  drawLineString: function (c, a, b) {
    a = OpenLayers.Util.applyDefaults({
      fill: false
    }, a);
    this.drawLinearRing(c, a, b)
  },
  drawLinearRing: function (c, a, b) {
    if (a.fill !== false) {
      this.setCanvasStyle('fill', a);
      this.renderPath(this.canvas, c, a, b, 'fill');
      if (this.hitDetection) {
        this.setHitContextStyle('fill', b, a);
        this.renderPath(this.hitContext, c, a, b, 'fill')
      }
    }
    if (a.stroke !== false) {
      this.setCanvasStyle('stroke', a);
      this.renderPath(this.canvas, c, a, b, 'stroke');
      if (this.hitDetection) {
        this.setHitContextStyle('stroke', b, a);
        this.renderPath(this.hitContext, c, a, b, 'stroke')
      }
    }
    this.setCanvasStyle('reset')
  },
  renderPath: function (c, k, a, e, h) {
    var f = k.components;
    var g = f.length;
    c.beginPath();
    var b = this.getLocalXY(f[0]);
    var l = b[0];
    var j = b[1];
    if (!isNaN(l) && !isNaN(j)) {
      c.moveTo(b[0], b[1]);
      for (var d = 1; d < g; ++d) {
        var m = this.getLocalXY(f[d]);
        c.lineTo(m[0], m[1])
      }
      if (h === 'fill') {
        c.fill()
      } else {
        c.stroke()
      }
    }
  },
  drawPolygon: function (f, c, e) {
    var d = f.components;
    var a = d.length;
    this.drawLinearRing(d[0], c, e);
    for (var b = 1; b < a; ++b) {
      this.canvas.globalCompositeOperation = 'destination-out';
      if (this.hitDetection) {
        this.hitContext.globalCompositeOperation = 'destination-out'
      }
      this.drawLinearRing(d[b], OpenLayers.Util.applyDefaults({
        stroke: false,
        fillOpacity: 1
      }, c), e);
      this.canvas.globalCompositeOperation = 'source-over';
      if (this.hitDetection) {
        this.hitContext.globalCompositeOperation = 'source-over'
      }
      this.drawLinearRing(d[b], OpenLayers.Util.applyDefaults({
        fill: false
      }, c), e)
    }
  },
  drawText: function (l, a) {
    var m = this.getLocalXY(l);
    this.setCanvasStyle('reset');
    this.canvas.fillStyle = a.fontColor;
    this.canvas.globalAlpha = a.fontOpacity || 1;
    var d = [
      a.fontStyle ? a.fontStyle : 'normal',
      'normal',
      a.fontWeight ? a.fontWeight : 'normal',
      a.fontSize ? a.fontSize : '1em',
      a.fontFamily ? a.fontFamily : 'sans-serif'
    ].join(' ');
    var c = a.label.split('\n');
    var f = c.length;
    if (this.canvas.fillText) {
      this.canvas.font = d;
      this.canvas.textAlign = OpenLayers.Renderer.Canvas.LABEL_ALIGN[a.labelAlign[0]] || 'center';
      this.canvas.textBaseline = OpenLayers.Renderer.Canvas.LABEL_ALIGN[a.labelAlign[1]] || 'middle';
      var j = OpenLayers.Renderer.Canvas.LABEL_FACTOR[a.labelAlign[1]];
      if (j == null) {
        j = - 0.5
      }
      var k = this.canvas.measureText('Mg').height || this.canvas.measureText('xx').width;
      m[1] += k * j * (f - 1);
      for (var e = 0; e < f; e++) {
        if (a.labelOutlineWidth) {
          this.canvas.save();
          this.canvas.globalAlpha = a.labelOutlineOpacity || a.fontOpacity || 1;
          this.canvas.strokeStyle = a.labelOutlineColor;
          this.canvas.lineWidth = a.labelOutlineWidth;
          this.canvas.strokeText(c[e], m[0], m[1] + (k * e) + 1);
          this.canvas.restore()
        }
        this.canvas.fillText(c[e], m[0], m[1] + (k * e))
      }
    } else {
      if (this.canvas.mozDrawText) {
        this.canvas.mozTextStyle = d;
        var b = OpenLayers.Renderer.Canvas.LABEL_FACTOR[a.labelAlign[0]];
        if (b == null) {
          b = - 0.5
        }
        var j = OpenLayers.Renderer.Canvas.LABEL_FACTOR[a.labelAlign[1]];
        if (j == null) {
          j = - 0.5
        }
        var k = this.canvas.mozMeasureText('xx');
        m[1] += k * (1 + (j * f));
        for (var e = 0; e < f; e++) {
          var h = m[0] + (b * this.canvas.mozMeasureText(c[e]));
          var g = m[1] + (e * k);
          this.canvas.translate(h, g);
          this.canvas.mozDrawText(c[e]);
          this.canvas.translate( - h, - g)
        }
      }
    }
    this.setCanvasStyle('reset')
  },
  getLocalXY: function (b) {
    var c = this.getResolution();
    var d = this.extent;
    var a = ((b.x - this.featureDx) / c + ( - d.left / c));
    var e = ((d.top / c) - b.y / c);
    return [a,
    e]
  },
  clear: function () {
    var a = this.root.height;
    var b = this.root.width;
    this.canvas.clearRect(0, 0, b, a);
    this.features = {
    };
    if (this.hitDetection) {
      this.hitContext.clearRect(0, 0, b, a)
    }
  },
  getFeatureIdFromEvent: function (g) {
    var c,
    i;
    if (this.hitDetection && this.root.style.display !== 'none') {
      if (!this.map.dragging) {
        var h = g.xy;
        var f = h.x | 0;
        var e = h.y | 0;
        var d = this.hitContext.getImageData(f, e, 1, 1).data;
        if (d[3] === 255) {
          var a = d[2] + (256 * (d[1] + (256 * d[0])));
          if (a) {
            c = 'OpenLayers_Feature_Vector_' + (a - 1 + this.hitOverflow);
            try {
              i = this.features[c][0]
            } catch (b) {
            }
          }
        }
      }
    }
    return i
  },
  eraseFeatures: function (b) {
    if (!(OpenLayers.Util.isArray(b))) {
      b = [
        b
      ]
    }
    for (var a = 0; a < b.length; ++a) {
      delete this.features[b[a].id]
    }
    this.redraw()
  },
  redraw: function () {
    if (!this.locked) {
      var j = this.root.height;
      var c = this.root.width;
      this.canvas.clearRect(0, 0, c, j);
      if (this.hitDetection) {
        this.hitContext.clearRect(0, 0, c, j)
      }
      var f = [
      ];
      var l,
      g,
      a;
      var h = (this.map.baseLayer && this.map.baseLayer.wrapDateLine) && this.map.getMaxExtent();
      for (var b in this.features) {
        if (!this.features.hasOwnProperty(b)) {
          continue
        }
        l = this.features[b][0];
        g = l.geometry;
        this.calculateFeatureDx(g.getBounds(), h);
        a = this.features[b][1];
        this.drawGeometry(g, a, l.id);
        if (a.label) {
          f.push([l,
          a])
        }
      }
      var k;
      for (var d = 0, e = f.length; d < e; ++d) {
        k = f[d];
        this.drawText(k[0].geometry.getCentroid(), k[1])
      }
    }
  },
  CLASS_NAME: 'OpenLayers.Renderer.Canvas'
}); OpenLayers.Renderer.Canvas.LABEL_ALIGN = {
  l: 'left',
  r: 'right',
  t: 'top',
  b: 'bottom'
}; OpenLayers.Renderer.Canvas.LABEL_FACTOR = {
  l: 0,
  r: - 1,
  t: 0,
  b: - 1
};
OpenLayers.Renderer.Canvas.drawImageScaleFactor = null; OpenLayers.ElementsIndexer = OpenLayers.Class({
  maxZIndex: null,
  order: null,
  indices: null,
  compare: null,
  initialize: function (a) {
    this.compare = a ? OpenLayers.ElementsIndexer.IndexingMethods.Z_ORDER_Y_ORDER : OpenLayers.ElementsIndexer.IndexingMethods.Z_ORDER_DRAWING_ORDER;
    this.clear()
  },
  insert: function (c) {
    if (this.exists(c)) {
      this.remove(c)
    }
    var f = c.id;
    this.determineZIndex(c);
    var d = - 1;
    var e = this.order.length;
    var a;
    while (e - d > 1) {
      a = parseInt((d + e) / 2);
      var b = this.compare(this, c, OpenLayers.Util.getElement(this.order[a]));
      if (b > 0) {
        d = a
      } else {
        e = a
      }
    }
    this.order.splice(e, 0, f);
    this.indices[f] = this.getZIndex(c);
    return this.getNextElement(e)
  },
  remove: function (b) {
    var d = b.id;
    var a = OpenLayers.Util.indexOf(this.order, d);
    if (a >= 0) {
      this.order.splice(a, 1);
      delete this.indices[d];
      if (this.order.length > 0) {
        var c = this.order[this.order.length - 1];
        this.maxZIndex = this.indices[c]
      } else {
        this.maxZIndex = 0
      }
    }
  },
  clear: function () {
    this.order = [
    ];
    this.indices = {
    };
    this.maxZIndex = 0
  },
  exists: function (a) {
    return (this.indices[a.id] != null)
  },
  getZIndex: function (a) {
    return a._style.graphicZIndex
  },
  determineZIndex: function (a) {
    var b = a._style.graphicZIndex;
    if (b == null) {
      b = this.maxZIndex;
      a._style.graphicZIndex = b
    } else {
      if (b > this.maxZIndex) {
        this.maxZIndex = b
      }
    }
  },
  getNextElement: function (b) {
    var a = b + 1;
    if (a < this.order.length) {
      var c = OpenLayers.Util.getElement(this.order[a]);
      if (c == undefined) {
        c = this.getNextElement(a)
      }
      return c
    } else {
      return null
    }
  },
  CLASS_NAME: 'OpenLayers.ElementsIndexer'
}); OpenLayers.ElementsIndexer.IndexingMethods = {
  Z_ORDER: function (e, d, b) {
    var a = e.getZIndex(d);
    var f = 0;
    if (b) {
      var c = e.getZIndex(b);
      f = a - c
    }
    return f
  },
  Z_ORDER_DRAWING_ORDER: function (c, b, a) {
    var d = OpenLayers.ElementsIndexer.IndexingMethods.Z_ORDER(c, b, a);
    if (a && d == 0) {
      d = 1
    }
    return d
  },
  Z_ORDER_Y_ORDER: function (d, c, b) {
    var e = OpenLayers.ElementsIndexer.IndexingMethods.Z_ORDER(d, c, b);
    if (b && e === 0) {
      var a = b._boundsBottom - c._boundsBottom;
      e = (a === 0) ? 1 : a
    }
    return e
  }
}; OpenLayers.Renderer.Elements = OpenLayers.Class(OpenLayers.Renderer, {
  rendererRoot: null,
  root: null,
  vectorRoot: null,
  textRoot: null,
  xmlns: null,
  xOffset: 0,
  indexer: null,
  BACKGROUND_ID_SUFFIX: '_background',
  LABEL_ID_SUFFIX: '_label',
  LABEL_OUTLINE_SUFFIX: '_outline',
  initialize: function (a, b) {
    OpenLayers.Renderer.prototype.initialize.apply(this, arguments);
    this.rendererRoot = this.createRenderRoot();
    this.root = this.createRoot('_root');
    this.vectorRoot = this.createRoot('_vroot');
    this.textRoot = this.createRoot('_troot');
    this.root.appendChild(this.vectorRoot);
    this.root.appendChild(this.textRoot);
    this.rendererRoot.appendChild(this.root);
    this.container.appendChild(this.rendererRoot);
    if (b && (b.zIndexing || b.yOrdering)) {
      this.indexer = new OpenLayers.ElementsIndexer(b.yOrdering)
    }
  },
  destroy: function () {
    this.clear();
    this.rendererRoot = null;
    this.root = null;
    this.xmlns = null;
    OpenLayers.Renderer.prototype.destroy.apply(this, arguments)
  },
  clear: function () {
    var b;
    var a = this.vectorRoot;
    if (a) {
      while (b = a.firstChild) {
        a.removeChild(b)
      }
    }
    a = this.textRoot;
    if (a) {
      while (b = a.firstChild) {
        a.removeChild(b)
      }
    }
    if (this.indexer) {
      this.indexer.clear()
    }
  },
  setExtent: function (e, g) {
    var c = OpenLayers.Renderer.prototype.setExtent.apply(this, arguments);
    var b = this.getResolution();
    if (this.map.baseLayer && this.map.baseLayer.wrapDateLine) {
      var a,
      d = e.getWidth() / this.map.getExtent().getWidth(),
      e = e.scale(1 / d),
      f = this.map.getMaxExtent();
      if (f.right > e.left && f.right < e.right) {
        a = true
      } else {
        if (f.left > e.left && f.left < e.right) {
          a = false
        }
      }
      if (a !== this.rightOfDateLine || g) {
        c = false;
        this.xOffset = a === true ? f.getWidth() / b : 0
      }
      this.rightOfDateLine = a
    }
    return c
  },
  getNodeType: function (b, a) {
  },
  drawGeometry: function (j, a, d) {
    var h = j.CLASS_NAME;
    var c = true;
    if ((h == 'OpenLayers.Geometry.Collection') || (h == 'OpenLayers.Geometry.MultiPoint') || (h == 'OpenLayers.Geometry.MultiLineString') || (h == 'OpenLayers.Geometry.MultiPolygon')) {
      for (var f = 0, g = j.components.length;
      f < g; f++) {
        c = this.drawGeometry(j.components[f], a, d) && c
      }
      return c
    }
    c = false;
    var e = false;
    if (a.display != 'none') {
      if (a.backgroundGraphic) {
        this.redrawBackgroundNode(j.id, j, a, d)
      } else {
        e = true
      }
      c = this.redrawNode(j.id, j, a, d)
    }
    if (c == false) {
      var b = document.getElementById(j.id);
      if (b) {
        if (b._style.backgroundGraphic) {
          e = true
        }
        b.parentNode.removeChild(b)
      }
    }
    if (e) {
      var b = document.getElementById(j.id + this.BACKGROUND_ID_SUFFIX);
      if (b) {
        b.parentNode.removeChild(b)
      }
    }
    return c
  },
  redrawNode: function (g, f, b, e) {
    b = this.applyDefaultSymbolizer(b);
    var c = this.nodeFactory(g, this.getNodeType(f, b));
    c._featureId = e;
    c._boundsBottom = f.getBounds().bottom;
    c._geometryClass = f.CLASS_NAME;
    c._style = b;
    var a = this.drawGeometryNode(c, f, b);
    if (a === false) {
      return false
    }
    c = a.node;
    if (this.indexer) {
      var d = this.indexer.insert(c);
      if (d) {
        this.vectorRoot.insertBefore(c, d)
      } else {
        this.vectorRoot.appendChild(c)
      }
    } else {
      if (c.parentNode !== this.vectorRoot) {
        this.vectorRoot.appendChild(c)
      }
    }
    this.postDraw(c);
    return a.complete
  },
  redrawBackgroundNode: function (e, d, b, c) {
    var a = OpenLayers.Util.extend({
    }, b);
    a.externalGraphic = a.backgroundGraphic;
    a.graphicXOffset = a.backgroundXOffset;
    a.graphicYOffset = a.backgroundYOffset;
    a.graphicZIndex = a.backgroundGraphicZIndex;
    a.graphicWidth = a.backgroundWidth || a.graphicWidth;
    a.graphicHeight = a.backgroundHeight || a.graphicHeight;
    a.backgroundGraphic = null;
    a.backgroundXOffset = null;
    a.backgroundYOffset = null;
    a.backgroundGraphicZIndex = null;
    return this.redrawNode(e + this.BACKGROUND_ID_SUFFIX, d, a, null)
  },
  drawGeometryNode: function (c, e, b) {
    b = b || c._style;
    var a = {
      isFilled: b.fill === undefined ? true : b.fill,
      isStroked: b.stroke === undefined ? !!b.strokeWidth : b.stroke
    };
    var d;
    switch (e.CLASS_NAME) {
      case 'OpenLayers.Geometry.Point':
        if (b.graphic === false) {
          a.isFilled = false;
          a.isStroked = false
        }
        d = this.drawPoint(c, e);
        break;
      case 'OpenLayers.Geometry.LineString':
        a.isFilled = false;
        d = this.drawLineString(c, e);
        break;
      case 'OpenLayers.Geometry.LinearRing':
        d = this.drawLinearRing(c, e);
        break;
      case 'OpenLayers.Geometry.Polygon':
        d = this.drawPolygon(c, e);
        break;
      case 'OpenLayers.Geometry.Rectangle':
        d = this.drawRectangle(c, e);
        break;
      default:
        break
    }
    c._options = a;
    if (d != false) {
      return {
        node: this.setStyle(c, b, a, e),
        complete: d
      }
  } else {
    return false
}
},
postDraw: function (a) {
},
drawPoint: function (a, b) {
},
drawLineString: function (a, b) {
},
drawLinearRing: function (a, b) {
},
drawPolygon: function (a, b) {
},
drawRectangle: function (a, b) {
},
drawCircle: function (a, b) {
},
removeText: function (c) {
var a = document.getElementById(c + this.LABEL_ID_SUFFIX);
if (a) {
  this.textRoot.removeChild(a)
}
var b = document.getElementById(c + this.LABEL_OUTLINE_SUFFIX);
if (b) {
  this.textRoot.removeChild(b)
}
},
getFeatureIdFromEvent: function (a) {
var d = a.target;
var b = d && d.correspondingUseElement;
var c = b ? b : (d || a.srcElement);
return c._featureId
},
eraseGeometry: function (g, f) {
if ((g.CLASS_NAME == 'OpenLayers.Geometry.MultiPoint') || (g.CLASS_NAME == 'OpenLayers.Geometry.MultiLineString') || (g.CLASS_NAME == 'OpenLayers.Geometry.MultiPolygon') || (g.CLASS_NAME == 'OpenLayers.Geometry.Collection')) {
  for (var d = 0, a = g.components.length;
  d < a; d++) {
    this.eraseGeometry(g.components[d], f)
  }
} else {
  var c = OpenLayers.Util.getElement(g.id);
  if (c && c.parentNode) {
    if (c.geometry) {
      c.geometry.destroy();
      c.geometry = null
    }
    c.parentNode.removeChild(c);
    if (this.indexer) {
      this.indexer.remove(c)
    }
    if (c._style.backgroundGraphic) {
      var b = g.id + this.BACKGROUND_ID_SUFFIX;
      var e = OpenLayers.Util.getElement(b);
      if (e && e.parentNode) {
        e.parentNode.removeChild(e)
      }
    }
  }
}
},
nodeFactory: function (c, a) {
var b = OpenLayers.Util.getElement(c);
if (b) {
  if (!this.nodeTypeCompare(b, a)) {
    b.parentNode.removeChild(b);
    b = this.nodeFactory(c, a)
  }
} else {
  b = this.createNode(a, c)
}
return b
},
nodeTypeCompare: function (b, a) {
},
createNode: function (a, b) {
},
moveRoot: function (b) {
var a = this.root;
if (b.root.parentNode == this.rendererRoot) {
  a = b.root
}
a.parentNode.removeChild(a);
b.rendererRoot.appendChild(a)
},
getRenderLayerId: function () {
return this.root.parentNode.parentNode.id
},
isComplexSymbol: function (a) {
return (a != 'circle') && !!a
},
CLASS_NAME: 'OpenLayers.Renderer.Elements'
}); OpenLayers.Renderer.SVG = OpenLayers.Class(OpenLayers.Renderer.Elements, {
xmlns: 'http://www.w3.org/2000/svg',
xlinkns: 'http://www.w3.org/1999/xlink',
MAX_PIXEL: 15000,
translationParameters: null,
symbolMetrics: null,
initialize: function (a) {
if (!this.supported()) {
  return
}
OpenLayers.Renderer.Elements.prototype.initialize.apply(this, arguments);
this.translationParameters = {
  x: 0,
  y: 0
};
this.symbolMetrics = {
}
},
supported: function () {
var a = 'http://www.w3.org/TR/SVG11/feature#';
return (document.implementation && (document.implementation.hasFeature('org.w3c.svg', '1.0') || document.implementation.hasFeature(a + 'SVG', '1.1') || document.implementation.hasFeature(a + 'BasicStructure', '1.1')))
},
inValidRange: function (a, e, b) {
var d = a + (b ? 0 : this.translationParameters.x);
var c = e + (b ? 0 : this.translationParameters.y);
return (d >= - this.MAX_PIXEL && d <= this.MAX_PIXEL && c >= - this.MAX_PIXEL && c <= this.MAX_PIXEL)
},
setExtent: function (c, e) {
var b = OpenLayers.Renderer.Elements.prototype.setExtent.apply(this, arguments);
var a = this.getResolution(),
g = - c.left / a,
f = c.top / a;
if (e) {
  this.left = g;
  this.top = f;
  var d = '0 0 ' + this.size.w + ' ' + this.size.h;
  this.rendererRoot.setAttributeNS(null, 'viewBox', d);
  this.translate(this.xOffset, 0);
  return true
} else {
  var h = this.translate(g - this.left + this.xOffset, f - this.top);
  if (!h) {
    this.setExtent(c, true)
  }
  return b && h
}
},
translate: function (a, c) {
if (!this.inValidRange(a, c, true)) {
  return false
} else {
  var b = '';
  if (a || c) {
    b = 'translate(' + a + ',' + c + ')'
  }
  this.root.setAttributeNS(null, 'transform', b);
  this.translationParameters = {
    x: a,
    y: c
  };
  return true
}
},
setSize: function (a) {
OpenLayers.Renderer.prototype.setSize.apply(this, arguments);
this.rendererRoot.setAttributeNS(null, 'width', this.size.w);
this.rendererRoot.setAttributeNS(null, 'height', this.size.h)
},
getNodeType: function (c, b) {
var a = null;
switch (c.CLASS_NAME) {
  case 'OpenLayers.Geometry.Point':
    if (b.externalGraphic) {
      a = 'image'
    } else {
      if (this.isComplexSymbol(b.graphicName)) {
        a = 'svg'
      } else {
        a = 'circle'
      }
    }
    break;
  case 'OpenLayers.Geometry.Rectangle':
    a = 'rect';
    break;
  case 'OpenLayers.Geometry.LineString':
    a = 'polyline';
    break;
  case 'OpenLayers.Geometry.LinearRing':
    a = 'polygon';
    break;
  case 'OpenLayers.Geometry.Polygon':
  case 'OpenLayers.Geometry.Curve':
    a = 'path';
    break;
  default:
    break
}
return a
},
setStyle: function (p, t, b) {
t = t || p._style;
b = b || p._options;
var v = t.title || t.graphicTitle;
if (v) {
  p.setAttributeNS(null, 'title', v);
  var o = p.getElementsByTagName('title');
  if (o.length > 0) {
    o[0].firstChild.textContent = v
  } else {
    var f = this.nodeFactory(null, 'title');
    f.textContent = v;
    p.appendChild(f)
  }
}
var k = parseFloat(p.getAttributeNS(null, 'r'));
var j = 1;
var d;
if (p._geometryClass == 'OpenLayers.Geometry.Point' && k) {
  p.style.visibility = '';
  if (t.graphic === false) {
    p.style.visibility = 'hidden'
  } else {
    if (t.externalGraphic) {
      d = this.getPosition(p);
      if (t.graphicWidth && t.graphicHeight) {
        p.setAttributeNS(null, 'preserveAspectRatio', 'none')
      }
      var n = t.graphicWidth || t.graphicHeight;
      var m = t.graphicHeight || t.graphicWidth;
      n = n ? n : t.pointRadius * 2;
      m = m ? m : t.pointRadius * 2;
      var u = (t.graphicXOffset != undefined) ? t.graphicXOffset : - (0.5 * n);
      var g = (t.graphicYOffset != undefined) ? t.graphicYOffset : - (0.5 * m);
      var a = t.graphicOpacity || t.fillOpacity;
      p.setAttributeNS(null, 'x', (d.x + u).toFixed());
      p.setAttributeNS(null, 'y', (d.y + g).toFixed());
      p.setAttributeNS(null, 'width', n);
      p.setAttributeNS(null, 'height', m);
      p.setAttributeNS(this.xlinkns, 'xlink:href', t.externalGraphic);
      p.setAttributeNS(null, 'style', 'opacity: ' + a);
      p.onclick = OpenLayers.Event.preventDefault
    } else {
      if (this.isComplexSymbol(t.graphicName)) {
        var c = t.pointRadius * 3;
        var l = c * 2;
        var e = this.importSymbol(t.graphicName);
        d = this.getPosition(p);
        j = this.symbolMetrics[e.id][0] * 3 / l;
        var h = p.parentNode;
        var i = p.nextSibling;
        if (h) {
          h.removeChild(p)
        }
        p.firstChild && p.removeChild(p.firstChild);
        p.appendChild(e.firstChild.cloneNode(true));
        p.setAttributeNS(null, 'viewBox', e.getAttributeNS(null, 'viewBox'));
        p.setAttributeNS(null, 'width', l);
        p.setAttributeNS(null, 'height', l);
        p.setAttributeNS(null, 'x', d.x - c);
        p.setAttributeNS(null, 'y', d.y - c);
        if (i) {
          h.insertBefore(p, i)
        } else {
          if (h) {
            h.appendChild(p)
          }
        }
      } else {
        p.setAttributeNS(null, 'r', t.pointRadius)
      }
    }
  }
  var s = t.rotation;
  if ((s !== undefined || p._rotation !== undefined) && d) {
    p._rotation = s;
    s |= 0;
    if (p.nodeName !== 'svg') {
      p.setAttributeNS(null, 'transform', 'rotate(' + s + ' ' + d.x + ' ' + d.y + ')')
    } else {
      var q = this.symbolMetrics[e.id];
      p.firstChild.setAttributeNS(null, 'transform', 'rotate(' + s + ' ' + q[1] + ' ' + q[2] + ')')
    }
  }
}
if (b.isFilled) {
  p.setAttributeNS(null, 'fill', t.fillColor);
  p.setAttributeNS(null, 'fill-opacity', t.fillOpacity)
} else {
  p.setAttributeNS(null, 'fill', 'none')
}
if (b.isStroked) {
  p.setAttributeNS(null, 'stroke', t.strokeColor);
  p.setAttributeNS(null, 'stroke-opacity', t.strokeOpacity);
  p.setAttributeNS(null, 'stroke-width', t.strokeWidth * j);
  p.setAttributeNS(null, 'stroke-linecap', t.strokeLinecap || 'round');
  p.setAttributeNS(null, 'stroke-linejoin', 'round');
  t.strokeDashstyle && p.setAttributeNS(null, 'stroke-dasharray', this.dashStyle(t, j))
} else {
  p.setAttributeNS(null, 'stroke', 'none')
}
if (t.pointerEvents) {
  p.setAttributeNS(null, 'pointer-events', t.pointerEvents)
}
if (t.cursor != null) {
  p.setAttributeNS(null, 'cursor', t.cursor)
}
return p
},
dashStyle: function (c, b) {
var a = c.strokeWidth * b;
var d = c.strokeDashstyle;
switch (d) {
  case 'solid':
    return 'none';
  case 'dot':
    return [1,
    4 * a].join();
  case 'dash':
    return [4 * a,
    4 * a].join();
  case 'dashdot':
    return [4 * a,
    4 * a,
    1,
    4 * a].join();
  case 'longdash':
    return [8 * a,
    4 * a].join();
  case 'longdashdot':
    return [8 * a,
    4 * a,
    1,
    4 * a].join();
  default:
    return OpenLayers.String.trim(d).replace(/\s+/g, ',')
}
},
createNode: function (a, c) {
var b = document.createElementNS(this.xmlns, a);
if (c) {
  b.setAttributeNS(null, 'id', c)
}
return b
},
nodeTypeCompare: function (b, a) {
return (a == b.nodeName)
},
createRenderRoot: function () {
var a = this.nodeFactory(this.container.id + '_svgRoot', 'svg');
a.style.display = 'block';
return a
},
createRoot: function (a) {
return this.nodeFactory(this.container.id + a, 'g')
},
createDefs: function () {
var a = this.nodeFactory(this.container.id + '_defs', 'defs');
this.rendererRoot.appendChild(a);
return a
},
drawPoint: function (a, b) {
return this.drawCircle(a, b, 1)
},
drawCircle: function (d, e, b) {
var c = this.getResolution();
var a = ((e.x - this.featureDx) / c + this.left);
var f = (this.top - e.y / c);
if (this.inValidRange(a, f)) {
  d.setAttributeNS(null, 'cx', a);
  d.setAttributeNS(null, 'cy', f);
  d.setAttributeNS(null, 'r', b);
  return d
} else {
  return false
}
},
drawLineString: function (b, c) {
var a = this.getComponentsString(c.components);
if (a.path) {
  b.setAttributeNS(null, 'points', a.path);
  return (a.complete ? b : null)
} else {
  return false
}
},
drawLinearRing: function (b, c) {
var a = this.getComponentsString(c.components);
if (a.path) {
  b.setAttributeNS(null, 'points', a.path);
  return (a.complete ? b : null)
} else {
  return false
}
},
drawPolygon: function (b, h) {
var g = '';
var i = true;
var a = true;
var c,
k;
for (var e = 0, f = h.components.length; e < f; e++) {
  g += ' M';
  c = this.getComponentsString(h.components[e].components, ' ');
  k = c.path;
  if (k) {
    g += ' ' + k;
    a = c.complete && a
  } else {
    i = false
  }
}
g += ' z';
if (i) {
  b.setAttributeNS(null, 'd', g);
  b.setAttributeNS(null, 'fill-rule', 'evenodd');
  return a ? b : null
} else {
  return false
}
},
drawRectangle: function (c, d) {
var b = this.getResolution();
var a = ((d.x - this.featureDx) / b + this.left);
var e = (this.top - d.y / b);
if (this.inValidRange(a, e)) {
  c.setAttributeNS(null, 'x', a);
  c.setAttributeNS(null, 'y', e);
  c.setAttributeNS(null, 'width', d.width / b);
  c.setAttributeNS(null, 'height', d.height / b);
  return c
} else {
  return false
}
},
drawText: function (f, b, p) {
var a = (!!b.labelOutlineWidth);
if (a) {
  var l = OpenLayers.Util.extend({
  }, b);
  l.fontColor = l.labelOutlineColor;
  l.fontStrokeColor = l.labelOutlineColor;
  l.fontStrokeWidth = b.labelOutlineWidth;
  if (b.labelOutlineOpacity) {
    l.fontOpacity = b.labelOutlineOpacity
  }
  delete l.labelOutlineWidth;
  this.drawText(f, l, p)
}
var c = this.getResolution();
var o = ((p.x - this.featureDx) / c + this.left);
var k = (p.y / c - this.top);
var q = (a) ? this.LABEL_OUTLINE_SUFFIX : this.LABEL_ID_SUFFIX;
var n = this.nodeFactory(f + q, 'text');
n.setAttributeNS(null, 'x', o);
n.setAttributeNS(null, 'y', - k);
if (b.fontColor) {
  n.setAttributeNS(null, 'fill', b.fontColor)
}
if (b.fontStrokeColor) {
  n.setAttributeNS(null, 'stroke', b.fontStrokeColor)
}
if (b.fontStrokeWidth) {
  n.setAttributeNS(null, 'stroke-width', b.fontStrokeWidth)
}
if (b.fontOpacity) {
  n.setAttributeNS(null, 'opacity', b.fontOpacity)
}
if (b.fontFamily) {
  n.setAttributeNS(null, 'font-family', b.fontFamily)
}
if (b.fontSize) {
  n.setAttributeNS(null, 'font-size', b.fontSize)
}
if (b.fontWeight) {
  n.setAttributeNS(null, 'font-weight', b.fontWeight)
}
if (b.fontStyle) {
  n.setAttributeNS(null, 'font-style', b.fontStyle)
}
if (b.labelSelect === true) {
  n.setAttributeNS(null, 'pointer-events', 'visible');
  n._featureId = f
} else {
  n.setAttributeNS(null, 'pointer-events', 'none')
}
var h = b.labelAlign || OpenLayers.Renderer.defaultSymbolizer.labelAlign;
n.setAttributeNS(null, 'text-anchor', OpenLayers.Renderer.SVG.LABEL_ALIGN[h[0]] || 'middle');
if (OpenLayers.IS_GECKO === true) {
  n.setAttributeNS(null, 'dominant-baseline', OpenLayers.Renderer.SVG.LABEL_ALIGN[h[1]] || 'central')
}
var d = b.label.split('\n');
var g = d.length;
while (n.childNodes.length > g) {
  n.removeChild(n.lastChild)
}
for (var e = 0; e < g; e++) {
  var j = this.nodeFactory(f + q + '_tspan_' + e, 'tspan');
  if (b.labelSelect === true) {
    j._featureId = f;
    j._geometry = p;
    j._geometryClass = p.CLASS_NAME
  }
  if (OpenLayers.IS_GECKO === false) {
    j.setAttributeNS(null, 'baseline-shift', OpenLayers.Renderer.SVG.LABEL_VSHIFT[h[1]] || '-35%')
  }
  j.setAttribute('x', o);
  if (e == 0) {
    var m = OpenLayers.Renderer.SVG.LABEL_VFACTOR[h[1]];
    if (m == null) {
      m = - 0.5
    }
    j.setAttribute('dy', (m * (g - 1)) + 'em')
  } else {
    j.setAttribute('dy', '1em')
  }
  j.textContent = (d[e] === '') ? ' ' : d[e];
  if (!j.parentNode) {
    n.appendChild(j)
  }
}
if (!n.parentNode) {
  this.textRoot.appendChild(n)
}
},
getComponentsString: function (d, c) {
var f = [
];
var a = true;
var e = d.length;
var j = [
];
var g,
h;
for (var b = 0; b < e; b++) {
  h = d[b];
  f.push(h);
  g = this.getShortString(h);
  if (g) {
    j.push(g)
  } else {
    if (b > 0) {
      if (this.getShortString(d[b - 1])) {
        j.push(this.clipLine(d[b], d[b - 1]))
      }
    }
    if (b < e - 1) {
      if (this.getShortString(d[b + 1])) {
        j.push(this.clipLine(d[b], d[b + 1]))
      }
    }
    a = false
  }
}
return {
  path: j.join(c || ','),
  complete: a
}
},
clipLine: function (e, h) {
if (h.equals(e)) {
  return ''
}
var f = this.getResolution();
var b = this.MAX_PIXEL - this.translationParameters.x;
var a = this.MAX_PIXEL - this.translationParameters.y;
var d = (h.x - this.featureDx) / f + this.left;
var j = this.top - h.y / f;
var c = (e.x - this.featureDx) / f + this.left;
var i = this.top - e.y / f;
var g;
if (c < - b || c > b) {
  g = (i - j) / (c - d);
  c = c < 0 ? - b : b;
  i = j + (c - d) * g
}
if (i < - a || i > a) {
  g = (c - d) / (i - j);
  i = i < 0 ? - a : a;
  c = d + (i - j) * g
}
return c + ',' + i
},
getShortString: function (b) {
var c = this.getResolution();
var a = ((b.x - this.featureDx) / c + this.left);
var d = (this.top - b.y / c);
if (this.inValidRange(a, d)) {
  return a + ',' + d
} else {
  return false
}
},
getPosition: function (a) {
return ({
  x: parseFloat(a.getAttributeNS(null, 'cx')),
  y: parseFloat(a.getAttributeNS(null, 'cy'))
})
},
importSymbol: function (f) {
if (!this.defs) {
  this.defs = this.createDefs()
}
var b = this.container.id + '-' + f;
var c = document.getElementById(b);
if (c != null) {
  return c
}
var e = OpenLayers.Renderer.symbol[f];
if (!e) {
  throw new Error(f + ' is not a valid symbol name')
}
var h = this.nodeFactory(b, 'symbol');
var d = this.nodeFactory(null, 'polygon');
h.appendChild(d);
var n = new OpenLayers.Bounds(Number.MAX_VALUE, Number.MAX_VALUE, 0, 0);
var l = [
];
var k,
j;
for (var g = 0; g < e.length; g = g + 2) {
  k = e[g];
  j = e[g + 1];
  n.left = Math.min(n.left, k);
  n.bottom = Math.min(n.bottom, j);
  n.right = Math.max(n.right, k);
  n.top = Math.max(n.top, j);
  l.push(k, ',', j)
}
d.setAttributeNS(null, 'points', l.join(' '));
var a = n.getWidth();
var m = n.getHeight();
var o = [
  n.left - a,
  n.bottom - m,
  a * 3,
  m * 3
];
h.setAttributeNS(null, 'viewBox', o.join(' '));
this.symbolMetrics[b] = [
  Math.max(a, m),
  n.getCenterLonLat().lon,
  n.getCenterLonLat().lat
];
this.defs.appendChild(h);
return h
},
getFeatureIdFromEvent: function (a) {
var c = OpenLayers.Renderer.Elements.prototype.getFeatureIdFromEvent.apply(this, arguments);
if (!c) {
  var b = a.target;
  c = b.parentNode && b != this.rendererRoot ? b.parentNode._featureId : undefined
}
return c
},
CLASS_NAME: 'OpenLayers.Renderer.SVG'
});
OpenLayers.Renderer.SVG.LABEL_ALIGN = {
l: 'start',
r: 'end',
b: 'bottom',
t: 'hanging'
};
OpenLayers.Renderer.SVG.LABEL_VSHIFT = {
t: '-70%',
b: '0'
};
OpenLayers.Renderer.SVG.LABEL_VFACTOR = {
t: 0,
b: - 1
};
OpenLayers.Renderer.SVG.preventDefault = function (a) {
OpenLayers.Event.preventDefault(a)
};
OpenLayers.Renderer.VML = OpenLayers.Class(OpenLayers.Renderer.Elements, {
xmlns: 'urn:schemas-microsoft-com:vml',
symbolCache: {
},
offset: null,
initialize: function (b) {
if (!this.supported()) {
  return
}
if (!document.namespaces.olv) {
  document.namespaces.add('olv', this.xmlns);
  var e = document.createStyleSheet();
  var c = [
    'shape',
    'rect',
    'oval',
    'fill',
    'stroke',
    'imagedata',
    'group',
    'textbox'
  ];
  for (var d = 0, a = c.length; d < a; d++) {
    e.addRule('olv\\:' + c[d], 'behavior: url(#default#VML); position: absolute; display: inline-block;')
  }
}
OpenLayers.Renderer.Elements.prototype.initialize.apply(this, arguments)
},
supported: function () {
return !!(document.namespaces)
},
setExtent: function (k, b) {
var a = OpenLayers.Renderer.Elements.prototype.setExtent.apply(this, arguments);
var d = this.getResolution();
var c = (k.left / d) | 0;
var h = (k.top / d - this.size.h) | 0;
if (b || !this.offset) {
  this.offset = {
    x: c,
    y: h
  };
  c = 0;
  h = 0
} else {
  c = c - this.offset.x;
  h = h - this.offset.y
}
var m = (c - this.xOffset) + ' ' + h;
this.root.coordorigin = m;
var j = [
  this.root,
  this.vectorRoot,
  this.textRoot
];
var g;
for (var e = 0, f = j.length; e < f; ++e) {
  g = j[e];
  var l = this.size.w + ' ' + this.size.h;
  g.coordsize = l
}
this.root.style.flip = 'y';
return a
},
setSize: function (f) {
OpenLayers.Renderer.prototype.setSize.apply(this, arguments);
var d = [
  this.rendererRoot,
  this.root,
  this.vectorRoot,
  this.textRoot
];
var c = this.size.w + 'px';
var g = this.size.h + 'px';
var b;
for (var e = 0, a = d.length; e < a; ++e) {
  b = d[e];
  b.style.width = c;
  b.style.height = g
}
},
getNodeType: function (c, b) {
var a = null;
switch (c.CLASS_NAME) {
  case 'OpenLayers.Geometry.Point':
    if (b.externalGraphic) {
      a = 'olv:rect'
    } else {
      if (this.isComplexSymbol(b.graphicName)) {
        a = 'olv:shape'
      } else {
        a = 'olv:oval'
      }
    }
    break;
  case 'OpenLayers.Geometry.Rectangle':
    a = 'olv:rect';
    break;
  case 'OpenLayers.Geometry.LineString':
  case 'OpenLayers.Geometry.LinearRing':
  case 'OpenLayers.Geometry.Polygon':
  case 'OpenLayers.Geometry.Curve':
    a = 'olv:shape';
    break;
  default:
    break
}
return a
},
setStyle: function (k, o, a, c) {
o = o || k._style;
a = a || k._options;
var b = o.fillColor;
var r = o.title || o.graphicTitle;
if (r) {
  k.title = r
}
if (k._geometryClass === 'OpenLayers.Geometry.Point') {
  if (o.externalGraphic) {
    a.isFilled = true;
    var j = o.graphicWidth || o.graphicHeight;
    var h = o.graphicHeight || o.graphicWidth;
    j = j ? j : o.pointRadius * 2;
    h = h ? h : o.pointRadius * 2;
    var m = this.getResolution();
    var q = (o.graphicXOffset != undefined) ? o.graphicXOffset : - (0.5 * j);
    var e = (o.graphicYOffset != undefined) ? o.graphicYOffset : - (0.5 * h);
    k.style.left = ((((c.x - this.featureDx) / m - this.offset.x) + q) | 0) + 'px';
    k.style.top = (((c.y / m - this.offset.y) - (e + h)) | 0) + 'px';
    k.style.width = j + 'px';
    k.style.height = h + 'px';
    k.style.flip = 'y';
    b = 'none';
    a.isStroked = false
  } else {
    if (this.isComplexSymbol(o.graphicName)) {
      var f = this.importSymbol(o.graphicName);
      k.path = f.path;
      k.coordorigin = f.left + ',' + f.bottom;
      var g = f.size;
      k.coordsize = g + ',' + g;
      this.drawCircle(k, c, o.pointRadius);
      k.style.flip = 'y'
    } else {
      this.drawCircle(k, c, o.pointRadius)
    }
  }
}
if (a.isFilled) {
  k.fillcolor = b
} else {
  k.filled = 'false'
}
var i = k.getElementsByTagName('fill');
var n = (i.length == 0) ? null : i[0];
if (!a.isFilled) {
  if (n) {
    k.removeChild(n)
  }
} else {
  if (!n) {
    n = this.createNode('olv:fill', k.id + '_fill')
  }
  n.opacity = o.fillOpacity;
  if (k._geometryClass === 'OpenLayers.Geometry.Point' && o.externalGraphic) {
    if (o.graphicOpacity) {
      n.opacity = o.graphicOpacity
    }
    n.src = o.externalGraphic;
    n.type = 'frame';
    if (!(o.graphicWidth && o.graphicHeight)) {
      n.aspect = 'atmost'
    }
  }
  if (n.parentNode != k) {
    k.appendChild(n)
  }
}
var l = o.rotation;
if ((l !== undefined || k._rotation !== undefined)) {
  k._rotation = l;
  if (o.externalGraphic) {
    this.graphicRotate(k, q, e, o);
    n.opacity = 0
  } else {
    if (k._geometryClass === 'OpenLayers.Geometry.Point') {
      k.style.rotation = l || 0
    }
  }
}
var p = k.getElementsByTagName('stroke');
var d = (p.length == 0) ? null : p[0];
if (!a.isStroked) {
  k.stroked = false;
  if (d) {
    d.on = false
  }
} else {
  if (!d) {
    d = this.createNode('olv:stroke', k.id + '_stroke');
    k.appendChild(d)
  }
  d.on = true;
  d.color = o.strokeColor;
  d.weight = o.strokeWidth + 'px';
  d.opacity = o.strokeOpacity;
  d.endcap = o.strokeLinecap == 'butt' ? 'flat' : (o.strokeLinecap || 'round');
  if (o.strokeDashstyle) {
    d.dashstyle = this.dashStyle(o)
  }
}
if (o.cursor != 'inherit' && o.cursor != null) {
  k.style.cursor = o.cursor
}
return k
},
graphicRotate: function (n, r, e, q) {
var q = q || n._style;
var o = q.rotation || 0;
var a,
j;
if (!(q.graphicWidth && q.graphicHeight)) {
  var s = new Image();
  s.onreadystatechange = OpenLayers.Function.bind(function () {
    if (s.readyState == 'complete' || s.readyState == 'interactive') {
      a = s.width / s.height;
      j = Math.max(q.pointRadius * 2, q.graphicWidth || 0, q.graphicHeight || 0);
      r = r * a;
      q.graphicWidth = j * a;
      q.graphicHeight = j;
      this.graphicRotate(n, r, e, q)
    }
  }, this);
  s.src = q.externalGraphic;
  return
} else {
  j = Math.max(q.graphicWidth, q.graphicHeight);
  a = q.graphicWidth / q.graphicHeight
}
var m = Math.round(q.graphicWidth || j * a);
var k = Math.round(q.graphicHeight || j);
n.style.width = m + 'px';
n.style.height = k + 'px';
var l = document.getElementById(n.id + '_image');
if (!l) {
  l = this.createNode('olv:imagedata', n.id + '_image');
  n.appendChild(l)
}
l.style.width = m + 'px';
l.style.height = k + 'px';
l.src = q.externalGraphic;
l.style.filter = 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'\', sizingMethod=\'scale\')';
var f = o * Math.PI / 180;
var h = Math.sin(f);
var d = Math.cos(f);
var g = 'progid:DXImageTransform.Microsoft.Matrix(M11=' + d + ',M12=' + ( - h) + ',M21=' + h + ',M22=' + d + ',SizingMethod=\'auto expand\')\n';
var b = q.graphicOpacity || q.fillOpacity;
if (b && b != 1) {
  g += 'progid:DXImageTransform.Microsoft.BasicImage(opacity=' + b + ')\n'
}
n.style.filter = g;
var p = new OpenLayers.Geometry.Point( - r, - e);
var c = new OpenLayers.Bounds(0, 0, m, k).toGeometry();
c.rotate(q.rotation, p);
var i = c.getBounds();
n.style.left = Math.round(parseInt(n.style.left) + i.left) + 'px';
n.style.top = Math.round(parseInt(n.style.top) - i.bottom) + 'px'
},
postDraw: function (a) {
a.style.visibility = 'visible';
var c = a._style.fillColor;
var b = a._style.strokeColor;
if (c == 'none' && a.fillcolor != c) {
  a.fillcolor = c
}
if (b == 'none' && a.strokecolor != b) {
  a.strokecolor = b
}
},
setNodeDimension: function (b, e) {
var d = e.getBounds();
if (d) {
  var a = this.getResolution();
  var c = new OpenLayers.Bounds(((d.left - this.featureDx) / a - this.offset.x) | 0, (d.bottom / a - this.offset.y) | 0, ((d.right - this.featureDx) / a - this.offset.x) | 0, (d.top / a - this.offset.y) | 0);
  b.style.left = c.left + 'px';
  b.style.top = c.top + 'px';
  b.style.width = c.getWidth() + 'px';
  b.style.height = c.getHeight() + 'px';
  b.coordorigin = c.left + ' ' + c.top;
  b.coordsize = c.getWidth() + ' ' + c.getHeight()
}
},
dashStyle: function (a) {
var c = a.strokeDashstyle;
switch (c) {
  case 'solid':
  case 'dot':
  case 'dash':
  case 'dashdot':
  case 'longdash':
  case 'longdashdot':
    return c;
  default:
    var b = c.split(/[ ,]/);
    if (b.length == 2) {
      if (1 * b[0] >= 2 * b[1]) {
        return 'longdash'
      }
      return (b[0] == 1 || b[1] == 1) ? 'dot' : 'dash'
    } else {
      if (b.length == 4) {
        return (1 * b[0] >= 2 * b[1]) ? 'longdashdot' : 'dashdot'
      }
    }
    return 'solid'
}
},
createNode: function (a, c) {
var b = document.createElement(a);
if (c) {
  b.id = c
}
b.unselectable = 'on';
b.onselectstart = OpenLayers.Function.False;
return b
},
nodeTypeCompare: function (c, b) {
var d = b;
var a = d.indexOf(':');
if (a != - 1) {
  d = d.substr(a + 1)
}
var e = c.nodeName;
a = e.indexOf(':');
if (a != - 1) {
  e = e.substr(a + 1)
}
return (d == e)
},
createRenderRoot: function () {
return this.nodeFactory(this.container.id + '_vmlRoot', 'div')
},
createRoot: function (a) {
return this.nodeFactory(this.container.id + a, 'olv:group')
},
drawPoint: function (a, b) {
return this.drawCircle(a, b, 1)
},
drawCircle: function (d, e, a) {
if (!isNaN(e.x) && !isNaN(e.y)) {
  var b = this.getResolution();
  d.style.left = ((((e.x - this.featureDx) / b - this.offset.x) | 0) - a) + 'px';
  d.style.top = (((e.y / b - this.offset.y) | 0) - a) + 'px';
  var c = a * 2;
  d.style.width = c + 'px';
  d.style.height = c + 'px';
  return d
}
return false
},
drawLineString: function (a, b) {
return this.drawLine(a, b, false)
},
drawLinearRing: function (a, b) {
return this.drawLine(a, b, true)
},
drawLine: function (b, k, g) {
this.setNodeDimension(b, k);
var c = this.getResolution();
var a = k.components.length;
var e = new Array(a);
var h,
l,
j;
for (var f = 0; f < a; f++) {
  h = k.components[f];
  l = ((h.x - this.featureDx) / c - this.offset.x) | 0;
  j = (h.y / c - this.offset.y) | 0;
  e[f] = ' ' + l + ',' + j + ' l '
}
var d = (g) ? ' x e' : ' e';
b.path = 'm' + e.join('') + d;
return b
},
drawPolygon: function (c, m) {
this.setNodeDimension(c, m);
var d = this.getResolution();
var r = [
];
var e,
k,
o,
a,
g,
b,
f,
q,
h,
p,
n,
l;
for (e = 0, k = m.components.length; e < k; e++) {
  r.push('m');
  o = m.components[e].components;
  a = (e === 0);
  g = null;
  b = null;
  for (f = 0, q = o.length; f < q; f++) {
    h = o[f];
    n = ((h.x - this.featureDx) / d - this.offset.x) | 0;
    l = (h.y / d - this.offset.y) | 0;
    p = ' ' + n + ',' + l;
    r.push(p);
    if (f == 0) {
      r.push(' l')
    }
    if (!a) {
      if (!g) {
        g = p
      } else {
        if (g != p) {
          if (!b) {
            b = p
          } else {
            if (b != p) {
              a = true
            }
          }
        }
      }
    }
  }
  r.push(a ? ' x ' : ' ')
}
r.push('e');
c.path = r.join('');
return c
},
drawRectangle: function (b, c) {
var a = this.getResolution();
b.style.left = (((c.x - this.featureDx) / a - this.offset.x) | 0) + 'px';
b.style.top = ((c.y / a - this.offset.y) | 0) + 'px';
b.style.width = ((c.width / a) | 0) + 'px';
b.style.height = ((c.height / a) | 0) + 'px';
return b
},
drawText: function (d, a, h) {
var g = this.nodeFactory(d + this.LABEL_ID_SUFFIX, 'olv:rect');
var f = this.nodeFactory(d + this.LABEL_ID_SUFFIX + '_textbox', 'olv:textbox');
var c = this.getResolution();
g.style.left = (((h.x - this.featureDx) / c - this.offset.x) | 0) + 'px';
g.style.top = ((h.y / c - this.offset.y) | 0) + 'px';
g.style.flip = 'y';
f.innerText = a.label;
if (a.cursor != 'inherit' && a.cursor != null) {
  f.style.cursor = a.cursor
}
if (a.fontColor) {
  f.style.color = a.fontColor
}
if (a.fontOpacity) {
  f.style.filter = 'alpha(opacity=' + (a.fontOpacity * 100) + ')'
}
if (a.fontFamily) {
  f.style.fontFamily = a.fontFamily
}
if (a.fontSize) {
  f.style.fontSize = a.fontSize
}
if (a.fontWeight) {
  f.style.fontWeight = a.fontWeight
}
if (a.fontStyle) {
  f.style.fontStyle = a.fontStyle
}
if (a.labelSelect === true) {
  g._featureId = d;
  f._featureId = d;
  f._geometry = h;
  f._geometryClass = h.CLASS_NAME
}
f.style.whiteSpace = 'nowrap';
f.inset = '1px,0px,0px,0px';
if (!g.parentNode) {
  g.appendChild(f);
  this.textRoot.appendChild(g)
}
var e = a.labelAlign || 'cm';
if (e.length == 1) {
  e += 'm'
}
var i = f.clientWidth * (OpenLayers.Renderer.VML.LABEL_SHIFT[e.substr(0, 1)]);
var b = f.clientHeight * (OpenLayers.Renderer.VML.LABEL_SHIFT[e.substr(1, 1)]);
g.style.left = parseInt(g.style.left) - i - 1 + 'px';
g.style.top = parseInt(g.style.top) + b + 'px'
},
moveRoot: function (b) {
var a = this.map.getLayer(b.container.id);
if (a instanceof OpenLayers.Layer.Vector.RootContainer) {
  a = this.map.getLayer(this.container.id)
}
a && a.renderer.clear();
OpenLayers.Renderer.Elements.prototype.moveRoot.apply(this, arguments);
a && a.redraw()
},
importSymbol: function (d) {
var b = this.container.id + '-' + d;
var a = this.symbolCache[b];
if (a) {
  return a
}
var c = OpenLayers.Renderer.symbol[d];
if (!c) {
  throw new Error(d + ' is not a valid symbol name')
}
var k = new OpenLayers.Bounds(Number.MAX_VALUE, Number.MAX_VALUE, 0, 0);
var e = [
  'm'
];
for (var f = 0; f < c.length; f = f + 2) {
  var h = c[f];
  var g = c[f + 1];
  k.left = Math.min(k.left, h);
  k.bottom = Math.min(k.bottom, g);
  k.right = Math.max(k.right, h);
  k.top = Math.max(k.top, g);
  e.push(h);
  e.push(g);
  if (f == 0) {
    e.push('l')
  }
}
e.push('x e');
var l = e.join(' ');
var j = (k.getWidth() - k.getHeight()) / 2;
if (j > 0) {
  k.bottom = k.bottom - j;
  k.top = k.top + j
} else {
  k.left = k.left + j;
  k.right = k.right - j
}
a = {
  path: l,
  size: k.getWidth(),
  left: k.left,
  bottom: k.bottom
};
this.symbolCache[b] = a;
return a
},
CLASS_NAME: 'OpenLayers.Renderer.VML'
});
OpenLayers.Renderer.VML.LABEL_SHIFT = {
l: 0,
c: 0.5,
r: 1,
t: 0,
m: 0.5,
b: 1
};
OpenLayers.Layer.Vector = OpenLayers.Class(OpenLayers.Layer, {
isBaseLayer: false,
isFixed: false,
features: null,
filter: null,
selectedFeatures: null,
unrenderedFeatures: null,
reportError: true,
style: null,
styleMap: null,
strategies: null,
protocol: null,
renderers: [
'SVG',
'VML',
'Canvas'
],
renderer: null,
rendererOptions: null,
geometryType: null,
drawn: false,
ratio: 1,
initialize: function (c, b) {
OpenLayers.Layer.prototype.initialize.apply(this, arguments);
if (!this.renderer || !this.renderer.supported()) {
  this.assignRenderer()
}
if (!this.renderer || !this.renderer.supported()) {
  this.renderer = null;
  this.displayError()
}
if (!this.styleMap) {
  this.styleMap = new OpenLayers.StyleMap()
}
this.features = [
];
this.selectedFeatures = [
];
this.unrenderedFeatures = {
};
if (this.strategies) {
  for (var d = 0, a = this.strategies.length;
  d < a; d++) {
    this.strategies[d].setLayer(this)
  }
}
},
destroy: function () {
if (this.strategies) {
  var c,
  b,
  a;
  for (b = 0, a = this.strategies.length; b < a; b++) {
    c = this.strategies[b];
    if (c.autoDestroy) {
      c.destroy()
    }
  }
  this.strategies = null
}
if (this.protocol) {
  if (this.protocol.autoDestroy) {
    this.protocol.destroy()
  }
  this.protocol = null
}
this.destroyFeatures();
this.features = null;
this.selectedFeatures = null;
this.unrenderedFeatures = null;
if (this.renderer) {
  this.renderer.destroy()
}
this.renderer = null;
this.geometryType = null;
this.drawn = null;
OpenLayers.Layer.prototype.destroy.apply(this, arguments)
},
clone: function (e) {
if (e == null) {
  e = new OpenLayers.Layer.Vector(this.name, this.getOptions())
}
e = OpenLayers.Layer.prototype.clone.apply(this, [
  e
]);
var c = this.features;
var a = c.length;
var d = new Array(a);
for (var b = 0; b < a; ++b) {
  d[b] = c[b].clone()
}
e.features = d;
return e
},
refresh: function (a) {
if (this.calculateInRange() && this.visibility) {
  this.events.triggerEvent('refresh', a)
}
},
assignRenderer: function () {
for (var c = 0, a = this.renderers.length;
c < a; c++) {
  var b = this.renderers[c];
  var d = (typeof b == 'function') ? b : OpenLayers.Renderer[b];
  if (d && d.prototype.supported()) {
    this.renderer = new d(this.div, this.rendererOptions);
    break
  }
}
},
displayError: function () {
if (this.reportError) {
  OpenLayers.Console.userError(OpenLayers.i18n('browserNotSupported', {
    renderers: this.renderers.join('\n')
  }))
}
},
setMap: function (b) {
OpenLayers.Layer.prototype.setMap.apply(this, arguments);
if (!this.renderer) {
  this.map.removeLayer(this)
} else {
  this.renderer.map = this.map;
  var a = this.map.getSize();
  a.w = a.w * this.ratio;
  a.h = a.h * this.ratio;
  this.renderer.setSize(a)
}
},
afterAdd: function () {
if (this.strategies) {
  var c,
  b,
  a;
  for (b = 0, a = this.strategies.length;
  b < a; b++) {
    c = this.strategies[b];
    if (c.autoActivate) {
      c.activate()
    }
  }
}
},
removeMap: function (c) {
this.drawn = false;
if (this.strategies) {
  var d,
  b,
  a;
  for (b = 0, a = this.strategies.length; b < a; b++) {
    d = this.strategies[b];
    if (d.autoActivate) {
      d.deactivate()
    }
  }
}
},
onMapResize: function () {
OpenLayers.Layer.prototype.onMapResize.apply(this, arguments);
var a = this.map.getSize();
a.w = a.w * this.ratio;
a.h = a.h * this.ratio;
this.renderer.setSize(a)
},
moveTo: function (a, b, l) {
OpenLayers.Layer.prototype.moveTo.apply(this, arguments);
var c = true;
if (!l) {
  this.renderer.root.style.visibility = 'hidden';
  var k = this.map.getSize(),
  h = k.w,
  f = k.h,
  e = (h / 2 * this.ratio) - h / 2,
  d = (f / 2 * this.ratio) - f / 2;
  e += this.map.layerContainerOriginPx.x;
  e = - Math.round(e);
  d += this.map.layerContainerOriginPx.y;
  d = - Math.round(d);
  this.div.style.left = e + 'px';
  this.div.style.top = d + 'px';
  var m = this.map.getExtent().scale(this.ratio);
  c = this.renderer.setExtent(m, b);
  this.renderer.root.style.visibility = 'visible';
  if (OpenLayers.IS_GECKO === true) {
    this.div.scrollLeft = this.div.scrollLeft
  }
  if (!b && c) {
    for (var g in this.unrenderedFeatures) {
      var n = this.unrenderedFeatures[g];
      this.drawFeature(n)
    }
  }
}
if (!this.drawn || b || !c) {
  this.drawn = true;
  var n;
  for (var g = 0, j = this.features.length; g < j; g++) {
    this.renderer.locked = (g !== (j - 1));
    n = this.features[g];
    this.drawFeature(n)
  }
}
},
display: function (a) {
OpenLayers.Layer.prototype.display.apply(this, arguments);
var b = this.div.style.display;
if (b != this.renderer.root.style.display) {
  this.renderer.root.style.display = b
}
},
addFeatures: function (b, j) {
if (!(OpenLayers.Util.isArray(b))) {
  b = [
    b
  ]
}
var g = !j || !j.silent;
if (g) {
  var a = {
    features: b
  };
  var f = this.events.triggerEvent('beforefeaturesadded', a);
  if (f === false) {
    return
  }
  b = a.features
}
var d = [
];
for (var c = 0, e = b.length; c < e; c++) {
  if (c != (b.length - 1)) {
    this.renderer.locked = true
  } else {
    this.renderer.locked = false
  }
  var h = b[c];
  if (this.geometryType && !(h.geometry instanceof this.geometryType)) {
    throw new TypeError('addFeatures: component should be an ' + this.geometryType.prototype.CLASS_NAME)
  }
  h.layer = this;
  if (!h.style && this.style) {
    h.style = OpenLayers.Util.extend({
    }, this.style)
  }
  if (g) {
    if (this.events.triggerEvent('beforefeatureadded', {
      feature: h
    }) === false) {
      continue
    }
    this.preFeatureInsert(h)
  }
  d.push(h);
  this.features.push(h);
  this.drawFeature(h);
  if (g) {
    this.events.triggerEvent('featureadded', {
      feature: h
    });
    this.onFeatureInsert(h)
  }
}
if (g) {
  this.events.triggerEvent('featuresadded', {
    features: d
  })
}
},
removeFeatures: function (e, a) {
if (!e || e.length === 0) {
  return
}
if (e === this.features) {
  return this.removeAllFeatures(a)
}
if (!(OpenLayers.Util.isArray(e))) {
  e = [
    e
  ]
}
if (e === this.selectedFeatures) {
  e = e.slice()
}
var d = !a || !a.silent;
if (d) {
  this.events.triggerEvent('beforefeaturesremoved', {
    features: e
  })
}
for (var c = e.length - 1; c >= 0; c--) {
  if (c != 0 && e[c - 1].geometry) {
    this.renderer.locked = true
  } else {
    this.renderer.locked = false
  }
  var b = e[c];
  delete this.unrenderedFeatures[b.id];
  if (d) {
    this.events.triggerEvent('beforefeatureremoved', {
      feature: b
    })
  }
  this.features = OpenLayers.Util.removeItem(this.features, b);
  b.layer = null;
  if (b.geometry) {
    this.renderer.eraseFeatures(b)
  }
  if (OpenLayers.Util.indexOf(this.selectedFeatures, b) != - 1) {
    OpenLayers.Util.removeItem(this.selectedFeatures, b)
  }
  if (d) {
    this.events.triggerEvent('featureremoved', {
      feature: b
    })
  }
}
if (d) {
  this.events.triggerEvent('featuresremoved', {
    features: e
  })
}
},
removeAllFeatures: function (a) {
var d = !a || !a.silent;
var e = this.features;
if (d) {
  this.events.triggerEvent('beforefeaturesremoved', {
    features: e
  })
}
var c;
for (var b = e.length - 1; b >= 0; b--) {
  c = e[b];
  if (d) {
    this.events.triggerEvent('beforefeatureremoved', {
      feature: c
    })
  }
  c.layer = null;
  if (d) {
    this.events.triggerEvent('featureremoved', {
      feature: c
    })
  }
}
this.renderer.clear();
this.features = [
];
this.unrenderedFeatures = {
};
this.selectedFeatures = [
];
if (d) {
  this.events.triggerEvent('featuresremoved', {
    features: e
  })
}
},
destroyFeatures: function (d, a) {
var c = (d == undefined);
if (c) {
  d = this.features
}
if (d) {
  this.removeFeatures(d, a);
  for (var b = d.length - 1; b >= 0; b--) {
    d[b].destroy()
  }
}
},
drawFeature: function (a, b) {
if (!this.drawn) {
  return
}
if (typeof b != 'object') {
  if (!b && a.state === OpenLayers.State.DELETE) {
    b = 'delete'
  }
  var c = b || a.renderIntent;
  b = a.style || this.style;
  if (!b) {
    b = this.styleMap.createSymbolizer(a, c)
  }
}
var d = this.renderer.drawFeature(a, b);
if (d === false || d === null) {
  this.unrenderedFeatures[a.id] = a
} else {
  delete this.unrenderedFeatures[a.id]
}
},
eraseFeatures: function (a) {
this.renderer.eraseFeatures(a)
},
getFeatureFromEvent: function (a) {
if (!this.renderer) {
  throw new Error('getFeatureFromEvent called on layer with no renderer. This usually means you destroyed a layer, but not some handler which is associated with it.')
}
var b = null;
var c = this.renderer.getFeatureIdFromEvent(a);
if (c) {
  if (typeof c === 'string') {
    b = this.getFeatureById(c)
  } else {
    b = c
  }
}
return b
},
getFeatureBy: function (e, d) {
var c = null;
for (var b = 0, a = this.features.length; b < a; ++b) {
  if (this.features[b][e] == d) {
    c = this.features[b];
    break
  }
}
return c
},
getFeatureById: function (a) {
return this.getFeatureBy('id', a)
},
getFeatureByFid: function (a) {
return this.getFeatureBy('fid', a)
},
getFeaturesByAttribute: function (d, e) {
var c,
b,
a = this.features.length,
f = [
];
for (c = 0; c < a; c++) {
  b = this.features[c];
  if (b && b.attributes) {
    if (b.attributes[d] === e) {
      f.push(b)
    }
  }
}
return f
},
onFeatureInsert: function (a) {
},
preFeatureInsert: function (a) {
},
getDataExtent: function () {
var b = null;
var d = this.features;
if (d && (d.length > 0)) {
  var e = null;
  for (var c = 0, a = d.length; c < a; c++) {
    e = d[c].geometry;
    if (e) {
      if (b === null) {
        b = new OpenLayers.Bounds()
      }
      b.extend(e.getBounds())
    }
  }
}
return b
},
CLASS_NAME: 'OpenLayers.Layer.Vector'
});
OpenLayers.Layer.Vector.RootContainer = OpenLayers.Class(OpenLayers.Layer.Vector, {
displayInLayerSwitcher: false,
layers: null,
display: function () {
},
getFeatureFromEvent: function (a) {
var d = this.layers;
var c;
for (var b = 0; b < d.length; b++) {
  c = d[b].getFeatureFromEvent(a);
  if (c) {
    return c
  }
}
},
setMap: function (a) {
OpenLayers.Layer.Vector.prototype.setMap.apply(this, arguments);
this.collectRoots();
a.events.register('changelayer', this, this.handleChangeLayer)
},
removeMap: function (a) {
a.events.unregister('changelayer', this, this.handleChangeLayer);
this.resetRoots();
OpenLayers.Layer.Vector.prototype.removeMap.apply(this, arguments)
},
collectRoots: function () {
var b;
for (var a = 0; a < this.map.layers.length; ++a) {
  b = this.map.layers[a];
  if (OpenLayers.Util.indexOf(this.layers, b) != - 1) {
    b.renderer.moveRoot(this.renderer)
  }
}
},
resetRoots: function () {
var b;
for (var a = 0;
a < this.layers.length; ++a) {
  b = this.layers[a];
  if (this.renderer && b.renderer.getRenderLayerId() == this.id) {
    this.renderer.moveRoot(b.renderer)
  }
}
},
handleChangeLayer: function (a) {
var b = a.layer;
if (a.property == 'order' && OpenLayers.Util.indexOf(this.layers, b) != - 1) {
  this.resetRoots();
  this.collectRoots()
}
},
CLASS_NAME: 'OpenLayers.Layer.Vector.RootContainer'
});
OpenLayers.Format.WKT = OpenLayers.Class(OpenLayers.Format, {
initialize: function (a) {
this.regExes = {
  typeStr: /^\s*(\w+)\s*\(\s*(.*)\s*\)\s*$/,
  spaces: /\s+/,
  parenComma: /\)\s*,\s*\(/,
  doubleParenComma: /\)\s*\)\s*,\s*\(\s*\(/,
  trimParens: /^\s*\(?(.*?)\)?\s*$/
};
OpenLayers.Format.prototype.initialize.apply(this, [
  a
])
},
read: function (f) {
var e,
d,
h;
f = f.replace(/[\n\r]/g, ' ');
var g = this.regExes.typeStr.exec(f);
if (g) {
  d = g[1].toLowerCase();
  h = g[2];
  if (this.parse[d]) {
    e = this.parse[d].apply(this, [
      h
    ])
  }
  if (this.internalProjection && this.externalProjection) {
    if (e && e.CLASS_NAME == 'OpenLayers.Feature.Vector') {
      e.geometry.transform(this.externalProjection, this.internalProjection)
    } else {
      if (e && d != 'geometrycollection' && typeof e == 'object') {
        for (var c = 0, a = e.length; c < a; c++) {
          var b = e[c];
          b.geometry.transform(this.externalProjection, this.internalProjection)
        }
      }
    }
  }
}
return e
},
write: function (c) {
var g,
f,
e;
if (c.constructor == Array) {
  g = c;
  e = true
} else {
  g = [
    c
  ];
  e = false
}
var d = [
];
if (e) {
  d.push('GEOMETRYCOLLECTION(')
}
for (var b = 0, a = g.length; b < a; ++b) {
  if (e && b > 0) {
    d.push(',')
  }
  f = g[b].geometry;
  d.push(this.extractGeometry(f))
}
if (e) {
  d.push(')')
}
return d.join('')
},
extractGeometry: function (d) {
var b = d.CLASS_NAME.split('.') [2].toLowerCase();
if (!this.extract[b]) {
  return null
}
if (this.internalProjection && this.externalProjection) {
  d = d.clone();
  d.transform(this.internalProjection, this.externalProjection)
}
var a = b == 'collection' ? 'GEOMETRYCOLLECTION' : b.toUpperCase();
var c = a + '(' + this.extract[b].apply(this, [
  d
]) + ')';
return c
},
extract: {
point: function (a) {
  return a.x + ' ' + a.y
},
multipoint: function (c) {
  var d = [
  ];
  for (var b = 0, a = c.components.length; b < a; ++b) {
    d.push('(' + this.extract.point.apply(this, [
      c.components[b]
    ]) + ')')
  }
  return d.join(',')
},
linestring: function (b) {
  var d = [
  ];
  for (var c = 0, a = b.components.length; c < a; ++c) {
    d.push(this.extract.point.apply(this, [
      b.components[c]
    ]))
  }
  return d.join(',')
},
multilinestring: function (c) {
  var d = [
  ];
  for (var b = 0, a = c.components.length; b < a; ++b) {
    d.push('(' + this.extract.linestring.apply(this, [
      c.components[b]
    ]) + ')')
  }
  return d.join(',')
},
polygon: function (c) {
  var d = [
  ];
  for (var b = 0, a = c.components.length; b < a; ++b) {
    d.push('(' + this.extract.linestring.apply(this, [
      c.components[b]
    ]) + ')')
  }
  return d.join(',')
},
multipolygon: function (d) {
  var c = [
  ];
  for (var b = 0, a = d.components.length; b < a; ++b) {
    c.push('(' + this.extract.polygon.apply(this, [
      d.components[b]
    ]) + ')')
  }
  return c.join(',')
},
collection: function (c) {
  var d = [
  ];
  for (var b = 0, a = c.components.length; b < a; ++b) {
    d.push(this.extractGeometry.apply(this, [
      c.components[b]
    ]))
  }
  return d.join(',')
}
},
parse: {
point: function (b) {
  var a = OpenLayers.String.trim(b).split(this.regExes.spaces);
  return new OpenLayers.Feature.Vector(new OpenLayers.Geometry.Point(a[0], a[1]))
},
multipoint: function (f) {
  var b;
  var d = OpenLayers.String.trim(f).split(',');
  var e = [
  ];
  for (var c = 0, a = d.length; c < a; ++c) {
    b = d[c].replace(this.regExes.trimParens, '$1');
    e.push(this.parse.point.apply(this, [
      b
    ]).geometry)
  }
  return new OpenLayers.Feature.Vector(new OpenLayers.Geometry.MultiPoint(e))
},
linestring: function (e) {
  var c = OpenLayers.String.trim(e).split(',');
  var d = [
  ];
  for (var b = 0, a = c.length; b < a; ++b) {
    d.push(this.parse.point.apply(this, [
      c[b]
    ]).geometry)
  }
  return new OpenLayers.Feature.Vector(new OpenLayers.Geometry.LineString(d))
},
multilinestring: function (f) {
  var c;
  var b = OpenLayers.String.trim(f).split(this.regExes.parenComma);
  var e = [
  ];
  for (var d = 0, a = b.length; d < a;
  ++d) {
    c = b[d].replace(this.regExes.trimParens, '$1');
    e.push(this.parse.linestring.apply(this, [
      c
    ]).geometry)
  }
  return new OpenLayers.Feature.Vector(new OpenLayers.Geometry.MultiLineString(e))
},
polygon: function (h) {
  var c,
  b,
  f;
  var g = OpenLayers.String.trim(h).split(this.regExes.parenComma);
  var e = [
  ];
  for (var d = 0, a = g.length;
  d < a; ++d) {
    c = g[d].replace(this.regExes.trimParens, '$1');
    b = this.parse.linestring.apply(this, [
      c
    ]).geometry;
    f = new OpenLayers.Geometry.LinearRing(b.components);
    e.push(f)
  }
  return new OpenLayers.Feature.Vector(new OpenLayers.Geometry.Polygon(e))
},
multipolygon: function (f) {
  var d;
  var b = OpenLayers.String.trim(f).split(this.regExes.doubleParenComma);
  var e = [
  ];
  for (var c = 0, a = b.length; c < a; ++c) {
    d = b[c].replace(this.regExes.trimParens, '$1');
    e.push(this.parse.polygon.apply(this, [
      d
    ]).geometry)
  }
  return new OpenLayers.Feature.Vector(new OpenLayers.Geometry.MultiPolygon(e))
},
geometrycollection: function (e) {
  e = e.replace(/,\s*([A-Za-z])/g, '|$1');
  var d = OpenLayers.String.trim(e).split('|');
  var c = [
  ];
  for (var b = 0, a = d.length; b < a; ++b) {
    c.push(OpenLayers.Format.WKT.prototype.read.apply(this, [
      d[b]
    ]))
  }
  return c
}
},
CLASS_NAME: 'OpenLayers.Format.WKT'
});
OpenLayers.Protocol = OpenLayers.Class({
format: null,
options: null,
autoDestroy: true,
defaultFilter: null,
initialize: function (a) {
a = a || {
};
OpenLayers.Util.extend(this, a);
this.options = a
},
mergeWithDefaultFilter: function (b) {
var a;
if (b && this.defaultFilter) {
  a = new OpenLayers.Filter.Logical({
    type: OpenLayers.Filter.Logical.AND,
    filters: [
      this.defaultFilter,
      b
    ]
  })
} else {
  a = b || this.defaultFilter || undefined
}
return a
},
destroy: function () {
this.options = null;
this.format = null
},
read: function (a) {
a = a || {
};
a.filter = this.mergeWithDefaultFilter(a.filter)
},
create: function () {
},
update: function () {
},
'delete': function () {
},
commit: function () {
},
abort: function (a) {
},
createCallback: function (c, a, b) {
return OpenLayers.Function.bind(function () {
  c.apply(this, [
    a,
    b
  ])
}, this)
},
CLASS_NAME: 'OpenLayers.Protocol'
});
OpenLayers.Protocol.Response = OpenLayers.Class({
code: null,
requestType: null,
last: true,
features: null,
data: null,
reqFeatures: null,
priv: null,
error: null,
initialize: function (a) {
OpenLayers.Util.extend(this, a)
},
success: function () {
return this.code > 0
},
CLASS_NAME: 'OpenLayers.Protocol.Response'
});
OpenLayers.Protocol.Response.SUCCESS = 1;
OpenLayers.Protocol.Response.FAILURE = 0;
OpenLayers.Format.JSON = OpenLayers.Class(OpenLayers.Format, {
indent: '    ',
space: ' ',
newline: '\n',
level: 0,
pretty: false,
nativeJSON: (function () {
return !!(window.JSON && typeof JSON.parse == 'function' && typeof JSON.stringify == 'function')
}) (),
read: function (json, filter) {
var object;
if (this.nativeJSON) {
  object = JSON.parse(json, filter)
} else {
  try {
    if (/^[\],:{}\s]*$/.test(json.replace(/\\["\\\/bfnrtu]/g, '@').replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {
      object = eval('(' + json + ')');
      if (typeof filter === 'function') {
        function walk(k, v) {
          if (v && typeof v === 'object') {
            for (var i in v) {
              if (v.hasOwnProperty(i)) {
                v[i] = walk(i, v[i])
              }
            }
          }
          return filter(k, v)
        }
        object = walk('', object)
      }
    }
  } catch (e) {
  }
}
if (this.keepData) {
  this.data = object
}
return object
},
write: function (e, c) {
this.pretty = !!c;
var a = null;
var b = typeof e;
if (this.serialize[b]) {
  try {
    a = (!this.pretty && this.nativeJSON) ? JSON.stringify(e)  : this.serialize[b].apply(this, [
      e
    ])
  } catch (d) {
    OpenLayers.Console.error('Trouble serializing: ' + d)
  }
}
return a
},
writeIndent: function () {
var b = [
];
if (this.pretty) {
  for (var a = 0; a < this.level; ++a) {
    b.push(this.indent)
  }
}
return b.join('')
},
writeNewline: function () {
return (this.pretty) ? this.newline : ''
},
writeSpace: function () {
return (this.pretty) ? this.space : ''
},
serialize: {
object: function (c) {
  if (c == null) {
    return 'null'
  }
  if (c.constructor == Date) {
    return this.serialize.date.apply(this, [
      c
    ])
  }
  if (c.constructor == Array) {
    return this.serialize.array.apply(this, [
      c
    ])
  }
  var f = [
    '{'
  ];
  this.level += 1;
  var d,
  b,
  e;
  var a = false;
  for (d in c) {
    if (c.hasOwnProperty(d)) {
      b = OpenLayers.Format.JSON.prototype.write.apply(this, [
        d,
        this.pretty
      ]);
      e = OpenLayers.Format.JSON.prototype.write.apply(this, [
        c[d],
        this.pretty
      ]);
      if (b != null && e != null) {
        if (a) {
          f.push(',')
        }
        f.push(this.writeNewline(), this.writeIndent(), b, ':', this.writeSpace(), e);
        a = true
      }
    }
  }
  this.level -= 1;
  f.push(this.writeNewline(), this.writeIndent(), '}');
  return f.join('')
},
array: function (e) {
  var c;
  var d = [
    '['
  ];
  this.level += 1;
  for (var b = 0, a = e.length; b < a; ++b) {
    c = OpenLayers.Format.JSON.prototype.write.apply(this, [
      e[b],
      this.pretty
    ]);
    if (c != null) {
      if (b > 0) {
        d.push(',')
      }
      d.push(this.writeNewline(), this.writeIndent(), c)
    }
  }
  this.level -= 1;
  d.push(this.writeNewline(), this.writeIndent(), ']');
  return d.join('')
},
string: function (b) {
  var a = {
    '': '\\b',
    '\t': '\\t',
    '\n': '\\n',
    '\f': '\\f',
    '\r': '\\r',
    '"': '\\"',
    '\\': '\\\\'
  };
  if (/["\\\x00-\x1f]/.test(b)) {
    return '"' + b.replace(/([\x00-\x1f\\"])/g, function (e, d) {
      var f = a[d];
      if (f) {
        return f
      }
      f = d.charCodeAt();
      return '\\u00' + Math.floor(f / 16).toString(16) + (f % 16).toString(16)
    }) + '"'
  }
  return '"' + b + '"'
},
number: function (a) {
  return isFinite(a) ? String(a)  : 'null'
},
'boolean': function (a) {
  return String(a)
},
date: function (a) {
  function b(c) {
    return (c < 10) ? '0' + c : c
  }
  return '"' + a.getFullYear() + '-' + b(a.getMonth() + 1) + '-' + b(a.getDate()) + 'T' + b(a.getHours()) + ':' + b(a.getMinutes()) + ':' + b(a.getSeconds()) + '"'
}
},
CLASS_NAME: 'OpenLayers.Format.JSON'
});
OpenLayers.Format.GeoJSON = OpenLayers.Class(OpenLayers.Format.JSON, {
ignoreExtraDims: false,
read: function (j, g, a) {
g = (g) ? g : 'FeatureCollection';
var d = null;
var c = null;
if (typeof j == 'string') {
  c = OpenLayers.Format.JSON.prototype.read.apply(this, [
    j,
    a
  ])
} else {
  c = j
}
if (!c) {
  OpenLayers.Console.error('Bad JSON: ' + j)
} else {
  if (typeof (c.type) != 'string') {
    OpenLayers.Console.error('Bad GeoJSON - no type: ' + j)
  } else {
    if (this.isValidType(c, g)) {
      switch (g) {
        case 'Geometry':
          try {
            d = this.parseGeometry(c)
          } catch (b) {
            OpenLayers.Console.error(b)
          }
          break;
        case 'Feature':
          try {
            d = this.parseFeature(c);
            d.type = 'Feature'
          } catch (b) {
            OpenLayers.Console.error(b)
          }
          break;
        case 'FeatureCollection':
          d = [
          ];
          switch (c.type) {
            case 'Feature':
              try {
                d.push(this.parseFeature(c))
              } catch (b) {
                d = null;
                OpenLayers.Console.error(b)
              }
              break;
            case 'FeatureCollection':
              for (var e = 0, f = c.features.length;
              e < f; ++e) {
                try {
                  d.push(this.parseFeature(c.features[e]))
                } catch (b) {
                  d = null;
                  OpenLayers.Console.error(b)
                }
              }
              break;
            default:
              try {
                var h = this.parseGeometry(c);
                d.push(new OpenLayers.Feature.Vector(h))
              } catch (b) {
                d = null;
                OpenLayers.Console.error(b)
              }
          }
          break
        }
    }
  }
}
return d
},
isValidType: function (c, a) {
var b = false;
switch (a) {
  case 'Geometry':
    if (OpenLayers.Util.indexOf(['Point',
    'MultiPoint',
    'LineString',
    'MultiLineString',
    'Polygon',
    'MultiPolygon',
    'Box',
    'GeometryCollection'], c.type) == - 1) {
      OpenLayers.Console.error('Unsupported geometry type: ' + c.type)
    } else {
      b = true
    }
    break;
  case 'FeatureCollection':
    b = true;
    break;
  default:
    if (c.type == a) {
      b = true
    } else {
      OpenLayers.Console.error('Cannot convert types from ' + c.type + ' to ' + a)
    }
}
return b
},
parseFeature: function (d) {
var b,
f,
a,
e;
a = (d.properties) ? d.properties : {
};
e = (d.geometry && d.geometry.bbox) || d.bbox;
try {
  f = this.parseGeometry(d.geometry)
} catch (c) {
  throw c
}
b = new OpenLayers.Feature.Vector(f, a);
if (e) {
  b.bounds = OpenLayers.Bounds.fromArray(e)
}
if (d.id) {
  b.fid = d.id
}
return b
},
parseGeometry: function (e) {
if (e == null) {
  return null
}
var g,
f = false;
if (e.type == 'GeometryCollection') {
  if (!(OpenLayers.Util.isArray(e.geometries))) {
    throw 'GeometryCollection must have geometries array: ' + e
  }
  var b = e.geometries.length;
  var d = new Array(b);
  for (var a = 0; a < b; ++a) {
    d[a] = this.parseGeometry.apply(this, [
      e.geometries[a]
    ])
  }
  g = new OpenLayers.Geometry.Collection(d);
  f = true
} else {
  if (!(OpenLayers.Util.isArray(e.coordinates))) {
    throw 'Geometry must have coordinates array: ' + e
  }
  if (!this.parseCoords[e.type.toLowerCase()]) {
    throw 'Unsupported geometry type: ' + e.type
  }
  try {
    g = this.parseCoords[e.type.toLowerCase()].apply(this, [
      e.coordinates
    ])
  } catch (c) {
    throw c
  }
}
if (this.internalProjection && this.externalProjection && !f) {
  g.transform(this.externalProjection, this.internalProjection)
}
return g
},
parseCoords: {
point: function (a) {
  if (this.ignoreExtraDims == false && a.length != 2) {
    throw 'Only 2D points are supported: ' + a
  }
  return new OpenLayers.Geometry.Point(a[0], a[1])
},
multipoint: function (f) {
  var c = [
  ];
  var e = null;
  for (var b = 0, a = f.length; b < a; ++b) {
    try {
      e = this.parseCoords.point.apply(this, [
        f[b]
      ])
    } catch (d) {
      throw d
    }
    c.push(e)
  }
  return new OpenLayers.Geometry.MultiPoint(c)
},
linestring: function (f) {
  var c = [
  ];
  var e = null;
  for (var b = 0, a = f.length;
  b < a; ++b) {
    try {
      e = this.parseCoords.point.apply(this, [
        f[b]
      ])
    } catch (d) {
      throw d
    }
    c.push(e)
  }
  return new OpenLayers.Geometry.LineString(c)
},
multilinestring: function (f) {
  var c = [
  ];
  var b = null;
  for (var d = 0, a = f.length; d < a; ++d) {
    try {
      b = this.parseCoords.linestring.apply(this, [
        f[d]
      ])
    } catch (e) {
      throw e
    }
    c.push(b)
  }
  return new OpenLayers.Geometry.MultiLineString(c)
},
polygon: function (g) {
  var f = [
  ];
  var e,
  b;
  for (var c = 0, a = g.length; c < a; ++c) {
    try {
      b = this.parseCoords.linestring.apply(this, [
        g[c]
      ])
    } catch (d) {
      throw d
    }
    e = new OpenLayers.Geometry.LinearRing(b.components);
    f.push(e)
  }
  return new OpenLayers.Geometry.Polygon(f)
},
multipolygon: function (f) {
  var b = [
  ];
  var e = null;
  for (var c = 0, a = f.length;
  c < a; ++c) {
    try {
      e = this.parseCoords.polygon.apply(this, [
        f[c]
      ])
    } catch (d) {
      throw d
    }
    b.push(e)
  }
  return new OpenLayers.Geometry.MultiPolygon(b)
},
box: function (a) {
  if (a.length != 2) {
    throw 'GeoJSON box coordinates must have 2 elements'
  }
  return new OpenLayers.Geometry.Polygon([new OpenLayers.Geometry.LinearRing([new OpenLayers.Geometry.Point(a[0][0], a[0][1]),
  new OpenLayers.Geometry.Point(a[1][0], a[0][1]),
  new OpenLayers.Geometry.Point(a[1][0], a[1][1]),
  new OpenLayers.Geometry.Point(a[0][0], a[1][1]),
  new OpenLayers.Geometry.Point(a[0][0], a[0][1])])])
}
},
write: function (e, d) {
var a = {
  type: null
};
if (OpenLayers.Util.isArray(e)) {
  a.type = 'FeatureCollection';
  var g = e.length;
  a.features = new Array(g);
  for (var c = 0; c < g; ++c) {
    var b = e[c];
    if (!b instanceof OpenLayers.Feature.Vector) {
      var f = 'FeatureCollection only supports collections of features: ' + b;
      throw f
    }
    a.features[c] = this.extract.feature.apply(this, [
      b
    ])
  }
} else {
  if (e.CLASS_NAME.indexOf('OpenLayers.Geometry') == 0) {
    a = this.extract.geometry.apply(this, [
      e
    ])
  } else {
    if (e instanceof OpenLayers.Feature.Vector) {
      a = this.extract.feature.apply(this, [
        e
      ]);
      if (e.layer && e.layer.projection) {
        a.crs = this.createCRSObject(e)
      }
    }
  }
}
return OpenLayers.Format.JSON.prototype.write.apply(this, [
  a,
  d
])
},
createCRSObject: function (b) {
var c = b.layer.projection.toString();
var a = {
};
if (c.match(/epsg:/i)) {
  var d = parseInt(c.substring(c.indexOf(':') + 1));
  if (d == 4326) {
    a = {
      type: 'name',
      properties: {
        name: 'urn:ogc:def:crs:OGC:1.3:CRS84'
      }
    }
  } else {
    a = {
      type: 'name',
      properties: {
        name: 'EPSG:' + d
      }
    }
  }
}
return a
},
extract: {
feature: function (c) {
  var b = this.extract.geometry.apply(this, [
    c.geometry
  ]);
  var a = {
    type: 'Feature',
    properties: c.attributes,
    geometry: b
  };
  if (c.fid != null) {
    a.id = c.fid
  }
  return a
},
geometry: function (d) {
  if (d == null) {
    return null
  }
  if (this.internalProjection && this.externalProjection) {
    d = d.clone();
    d.transform(this.internalProjection, this.externalProjection)
  }
  var a = d.CLASS_NAME.split('.') [2];
  var c = this.extract[a.toLowerCase()].apply(this, [
    d
  ]);
  var b;
  if (a == 'Collection') {
    b = {
      type: 'GeometryCollection',
      geometries: c
    }
  } else {
    b = {
      type: a,
      coordinates: c
    }
  }
  return b
},
point: function (a) {
  return [a.x,
  a.y]
},
multipoint: function (c) {
  var d = [
  ];
  for (var b = 0, a = c.components.length; b < a; ++b) {
    d.push(this.extract.point.apply(this, [
      c.components[b]
    ]))
  }
  return d
},
linestring: function (b) {
  var d = [
  ];
  for (var c = 0, a = b.components.length; c < a; ++c) {
    d.push(this.extract.point.apply(this, [
      b.components[c]
    ]))
  }
  return d
},
multilinestring: function (c) {
  var d = [
  ];
  for (var b = 0, a = c.components.length; b < a; ++b) {
    d.push(this.extract.linestring.apply(this, [
      c.components[b]
    ]))
  }
  return d
},
polygon: function (c) {
  var d = [
  ];
  for (var b = 0, a = c.components.length;
  b < a; ++b) {
    d.push(this.extract.linestring.apply(this, [
      c.components[b]
    ]))
  }
  return d
},
multipolygon: function (d) {
  var c = [
  ];
  for (var b = 0, a = d.components.length; b < a; ++b) {
    c.push(this.extract.polygon.apply(this, [
      d.components[b]
    ]))
  }
  return c
},
collection: function (c) {
  var a = c.components.length;
  var d = new Array(a);
  for (var b = 0;
  b < a; ++b) {
    d[b] = this.extract.geometry.apply(this, [
      c.components[b]
    ])
  }
  return d
}
},
CLASS_NAME: 'OpenLayers.Format.GeoJSON'
});
OpenLayers.Protocol.Script = OpenLayers.Class(OpenLayers.Protocol, {
url: null,
params: null,
callback: null,
callbackTemplate: 'OpenLayers.Protocol.Script.registry.${id}',
callbackKey: 'callback',
callbackPrefix: '',
scope: null,
format: null,
pendingRequests: null,
srsInBBOX: false,
initialize: function (a) {
a = a || {
};
this.params = {
};
this.pendingRequests = {
};
OpenLayers.Protocol.prototype.initialize.apply(this, arguments);
if (!this.format) {
  this.format = new OpenLayers.Format.GeoJSON()
}
if (!this.filterToParams && OpenLayers.Format.QueryStringFilter) {
  var b = new OpenLayers.Format.QueryStringFilter({
    srsInBBOX: this.srsInBBOX
  });
  this.filterToParams = function (c, d) {
    return b.write(c, d)
  }
}
},
read: function (b) {
OpenLayers.Protocol.prototype.read.apply(this, arguments);
b = OpenLayers.Util.applyDefaults(b, this.options);
b.params = OpenLayers.Util.applyDefaults(b.params, this.options.params);
if (b.filter && this.filterToParams) {
  b.params = this.filterToParams(b.filter, b.params)
}
var a = new OpenLayers.Protocol.Response({
  requestType: 'read'
});
var c = this.createRequest(b.url, b.params, OpenLayers.Function.bind(function (d) {
  a.data = d;
  this.handleRead(a, b)
}, this));
a.priv = c;
return a
},
createRequest: function (c, e, g) {
var f = OpenLayers.Protocol.Script.register(g);
var b = OpenLayers.String.format(this.callbackTemplate, {
  id: f
});
e = OpenLayers.Util.extend({
}, e);
e[this.callbackKey] = this.callbackPrefix + b;
c = OpenLayers.Util.urlAppend(c, OpenLayers.Util.getParameterString(e));
c = decodeURIComponent(c);
var a = document.createElement('script');
a.type = 'text/javascript';
a.src = c;
a.id = 'OpenLayers_Protocol_Script_' + f;
this.pendingRequests[a.id] = a;
var d = document.getElementsByTagName('head') [0];
d.appendChild(a);
return a
},
destroyRequest: function (a) {
OpenLayers.Protocol.Script.unregister(a.id.split('_').pop());
delete this.pendingRequests[a.id];
if (a.parentNode) {
  a.parentNode.removeChild(a)
}
},
handleRead: function (a, b) {
this.handleResponse(a, b)
},
handleResponse: function (a, b) {
if (b.callback) {
  if (a.data) {
    a.features = this.parseFeatures(a.data);
    a.code = OpenLayers.Protocol.Response.SUCCESS
  } else {
    a.code = OpenLayers.Protocol.Response.FAILURE
  }
  this.destroyRequest(a.priv);
  b.callback.call(b.scope, a)
}
},
parseFeatures: function (a) {
return this.format.read(a)
},
abort: function (a) {
if (a) {
  this.destroyRequest(a.priv)
} else {
  for (var b in this.pendingRequests) {
    this.destroyRequest(this.pendingRequests[b])
  }
}
},
destroy: function () {
this.abort();
delete this.params;
delete this.format;
OpenLayers.Protocol.prototype.destroy.apply(this)
},
CLASS_NAME: 'OpenLayers.Protocol.Script'
});
(function () {
var b = OpenLayers.Protocol.Script;
var a = 0;
b.registry = {
};
b.register = function (d) {
var c = 'c' + (++a);
b.registry[c] = function () {
  d.apply(this, arguments)
};
return c
};
b.unregister = function (c) {
delete b.registry[c]
}
}) ();
OpenLayers.Tile = OpenLayers.Class({
events: null,
eventListeners: null,
id: null,
layer: null,
url: null,
bounds: null,
size: null,
position: null,
isLoading: false,
initialize: function (e, a, f, c, d, b) {
this.layer = e;
this.position = a.clone();
this.setBounds(f);
this.url = c;
if (d) {
  this.size = d.clone()
}
this.id = OpenLayers.Util.createUniqueID('Tile_');
OpenLayers.Util.extend(this, b);
this.events = new OpenLayers.Events(this);
if (this.eventListeners instanceof Object) {
  this.events.on(this.eventListeners)
}
},
unload: function () {
if (this.isLoading) {
  this.isLoading = false;
  this.events.triggerEvent('unload')
}
},
destroy: function () {
this.layer = null;
this.bounds = null;
this.size = null;
this.position = null;
if (this.eventListeners) {
  this.events.un(this.eventListeners)
}
this.events.destroy();
this.eventListeners = null;
this.events = null
},
draw: function (b) {
if (!b) {
  this.clear()
}
var a = this.shouldDraw();
if (a && !b && this.events.triggerEvent('beforedraw') === false) {
  a = null
}
return a
},
shouldDraw: function () {
var b = false,
a = this.layer.maxExtent;
if (a) {
  var d = this.layer.map;
  var c = d.baseLayer.wrapDateLine && d.getMaxExtent();
  if (this.bounds.intersectsBounds(a, {
    inclusive: false,
    worldBounds: c
  })) {
    b = true
  }
}
return b || this.layer.displayOutsideMaxExtent
},
setBounds: function (c) {
c = c.clone();
if (this.layer.map.baseLayer.wrapDateLine) {
  var b = this.layer.map.getMaxExtent(),
  a = this.layer.map.getResolution();
  c = c.wrapDateLine(b, {
    leftTolerance: a,
    rightTolerance: a
  })
}
this.bounds = c
},
moveTo: function (b, a, c) {
if (c == null) {
  c = true
}
this.setBounds(b);
this.position = a.clone();
if (c) {
  this.draw()
}
},
clear: function (a) {
},
CLASS_NAME: 'OpenLayers.Tile'
});
OpenLayers.Tile.Image = OpenLayers.Class(OpenLayers.Tile, {
url: null,
imgDiv: null,
frame: null,
imageReloadAttempts: null,
layerAlphaHack: null,
asyncRequestId: null,
maxGetUrlLength: null,
canvasContext: null,
crossOriginKeyword: null,
initialize: function (e, a, f, c, d, b) {
OpenLayers.Tile.prototype.initialize.apply(this, arguments);
this.url = c;
this.layerAlphaHack = this.layer.alpha && OpenLayers.Util.alphaHack();
if (this.maxGetUrlLength != null || this.layer.gutter || this.layerAlphaHack) {
  this.frame = document.createElement('div');
  this.frame.style.position = 'absolute';
  this.frame.style.overflow = 'hidden'
}
if (this.maxGetUrlLength != null) {
  OpenLayers.Util.extend(this, OpenLayers.Tile.Image.IFrame)
}
},
destroy: function () {
if (this.imgDiv) {
  this.clear();
  this.imgDiv = null;
  this.frame = null
}
this.asyncRequestId = null;
OpenLayers.Tile.prototype.destroy.apply(this, arguments)
},
draw: function () {
var a = OpenLayers.Tile.prototype.draw.apply(this, arguments);
if (a) {
  if (this.layer != this.layer.map.baseLayer && this.layer.reproject) {
    this.bounds = this.getBoundsFromBaseLayer(this.position)
  }
  if (this.isLoading) {
    this._loadEvent = 'reload'
  } else {
    this.isLoading = true;
    this._loadEvent = 'loadstart'
  }
  this.renderTile();
  this.positionTile()
} else {
  if (a === false) {
    this.unload()
  }
}
return a
},
renderTile: function () {
if (this.layer.async) {
  var a = this.asyncRequestId = (this.asyncRequestId || 0) + 1;
  this.layer.getURLasync(this.bounds, function (b) {
    if (a == this.asyncRequestId) {
      this.url = b;
      this.initImage()
    }
  }, this)
} else {
  this.url = this.layer.getURL(this.bounds);
  this.initImage()
}
},
positionTile: function () {
var c = this.getTile().style,
a = this.frame ? this.size : this.layer.getImageSize(this.bounds),
b = 1;
if (this.layer instanceof OpenLayers.Layer.Grid) {
  b = this.layer.getServerResolution() / this.layer.map.getResolution()
}
c.left = this.position.x + 'px';
c.top = this.position.y + 'px';
c.width = Math.round(b * a.w) + 'px';
c.height = Math.round(b * a.h) + 'px'
},
clear: function () {
OpenLayers.Tile.prototype.clear.apply(this, arguments);
var a = this.imgDiv;
if (a) {
  var b = this.getTile();
  if (b.parentNode === this.layer.div) {
    this.layer.div.removeChild(b)
  }
  this.setImgSrc();
  if (this.layerAlphaHack === true) {
    a.style.filter = ''
  }
  OpenLayers.Element.removeClass(a, 'olImageLoadError')
}
this.canvasContext = null
},
getImage: function () {
if (!this.imgDiv) {
  this.imgDiv = OpenLayers.Tile.Image.IMAGE.cloneNode(false);
  var a = this.imgDiv.style;
  if (this.frame) {
    var c = 0,
    b = 0;
    if (this.layer.gutter) {
      c = this.layer.gutter / this.layer.tileSize.w * 100;
      b = this.layer.gutter / this.layer.tileSize.h * 100
    }
    a.left = - c + '%';
    a.top = - b + '%';
    a.width = (2 * c + 100) + '%';
    a.height = (2 * b + 100) + '%'
  }
  a.visibility = 'hidden';
  a.opacity = 0;
  if (this.layer.opacity < 1) {
    a.filter = 'alpha(opacity=' + (this.layer.opacity * 100) + ')'
  }
  a.position = 'absolute';
  if (this.layerAlphaHack) {
    a.paddingTop = a.height;
    a.height = '0';
    a.width = '100%'
  }
  if (this.frame) {
    this.frame.appendChild(this.imgDiv)
  }
}
return this.imgDiv
},
setImage: function (a) {
this.imgDiv = a
},
initImage: function () {
if (!this.url && !this.imgDiv) {
  this.isLoading = false;
  return
}
this.events.triggerEvent('beforeload');
this.layer.div.appendChild(this.getTile());
this.events.triggerEvent(this._loadEvent);
var a = this.getImage();
var b = a.getAttribute('src') || '';
if (this.url && OpenLayers.Util.isEquivalentUrl(b, this.url)) {
  this._loadTimeout = window.setTimeout(OpenLayers.Function.bind(this.onImageLoad, this), 0)
} else {
  this.stopLoading();
  if (this.crossOriginKeyword) {
    a.removeAttribute('crossorigin')
  }
  OpenLayers.Event.observe(a, 'load', OpenLayers.Function.bind(this.onImageLoad, this));
  OpenLayers.Event.observe(a, 'error', OpenLayers.Function.bind(this.onImageError, this));
  this.imageReloadAttempts = 0;
  this.setImgSrc(this.url)
}
},
setImgSrc: function (b) {
var a = this.imgDiv;
if (b) {
  a.style.visibility = 'hidden';
  a.style.opacity = 0;
  if (this.crossOriginKeyword) {
    if (b.substr(0, 5) !== 'data:') {
      a.setAttribute('crossorigin', this.crossOriginKeyword)
    } else {
      a.removeAttribute('crossorigin')
    }
  }
  a.src = b
} else {
  this.stopLoading();
  this.imgDiv = null;
  if (a.parentNode) {
    a.parentNode.removeChild(a)
  }
}
},
getTile: function () {
return this.frame ? this.frame : this.getImage()
},
createBackBuffer: function () {
if (!this.imgDiv || this.isLoading) {
  return
}
var a;
if (this.frame) {
  a = this.frame.cloneNode(false);
  a.appendChild(this.imgDiv)
} else {
  a = this.imgDiv
}
this.imgDiv = null;
return a
},
onImageLoad: function () {
var a = this.imgDiv;
this.stopLoading();
a.style.visibility = 'inherit';
a.style.opacity = this.layer.opacity;
this.isLoading = false;
this.canvasContext = null;
this.events.triggerEvent('loadend');
if (this.layerAlphaHack === true) {
  a.style.filter = 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'' + a.src + '\', sizingMethod=\'scale\')'
}
},
onImageError: function () {
var a = this.imgDiv;
if (a.src != null) {
  this.imageReloadAttempts++;
  if (this.imageReloadAttempts <= OpenLayers.IMAGE_RELOAD_ATTEMPTS) {
    this.setImgSrc(this.layer.getURL(this.bounds))
  } else {
    OpenLayers.Element.addClass(a, 'olImageLoadError');
    this.events.triggerEvent('loaderror');
    this.onImageLoad()
  }
}
},
stopLoading: function () {
OpenLayers.Event.stopObservingElement(this.imgDiv);
window.clearTimeout(this._loadTimeout);
delete this._loadTimeout
},
getCanvasContext: function () {
if (OpenLayers.CANVAS_SUPPORTED && this.imgDiv && !this.isLoading) {
  if (!this.canvasContext) {
    var a = document.createElement('canvas');
    a.width = this.size.w;
    a.height = this.size.h;
    this.canvasContext = a.getContext('2d');
    this.canvasContext.drawImage(this.imgDiv, 0, 0)
  }
  return this.canvasContext
}
},
CLASS_NAME: 'OpenLayers.Tile.Image'
});
OpenLayers.Tile.Image.IMAGE = (function () {
var a = new Image();
a.className = 'olTileImage';
a.galleryImg = 'no';
return a
}());
OpenLayers.Layer.HTTPRequest = OpenLayers.Class(OpenLayers.Layer, {
URL_HASH_FACTOR: (Math.sqrt(5) - 1) / 2,
url: null,
params: null,
reproject: false,
initialize: function (c, b, d, a) {
OpenLayers.Layer.prototype.initialize.apply(this, [
  c,
  a
]);
this.url = b;
if (!this.params) {
  this.params = OpenLayers.Util.extend({
  }, d)
}
},
destroy: function () {
this.url = null;
this.params = null;
OpenLayers.Layer.prototype.destroy.apply(this, arguments)
},
clone: function (a) {
if (a == null) {
  a = new OpenLayers.Layer.HTTPRequest(this.name, this.url, this.params, this.getOptions())
}
a = OpenLayers.Layer.prototype.clone.apply(this, [
  a
]);
return a
},
setUrl: function (a) {
this.url = a
},
mergeNewParams: function (b) {
this.params = OpenLayers.Util.extend(this.params, b);
var a = this.redraw();
if (this.map != null) {
  this.map.events.triggerEvent('changelayer', {
    layer: this,
    property: 'params'
  })
}
return a
},
redraw: function (a) {
if (a) {
  return this.mergeNewParams({
    _olSalt: Math.random()
  })
} else {
  return OpenLayers.Layer.prototype.redraw.apply(this, [
  ])
}
},
selectUrl: function (e, d) {
var c = 1;
for (var b = 0, a = e.length; b < a; b++) {
  c *= e.charCodeAt(b) * this.URL_HASH_FACTOR;
  c -= Math.floor(c)
}
return d[Math.floor(c * d.length)]
},
getFullRequestString: function (g, d) {
var b = d || this.url;
var f = OpenLayers.Util.extend({
}, this.params);
f = OpenLayers.Util.extend(f, g);
var e = OpenLayers.Util.getParameterString(f);
if (OpenLayers.Util.isArray(b)) {
  b = this.selectUrl(e, b)
}
var a = OpenLayers.Util.upperCaseObject(OpenLayers.Util.getParameters(b));
for (var c in f) {
  if (c.toUpperCase() in a) {
    delete f[c]
  }
}
e = OpenLayers.Util.getParameterString(f);
return OpenLayers.Util.urlAppend(b, e)
},
CLASS_NAME: 'OpenLayers.Layer.HTTPRequest'
});
OpenLayers.Layer.Grid = OpenLayers.Class(OpenLayers.Layer.HTTPRequest, {
tileSize: null,
tileOriginCorner: 'bl',
tileOrigin: null,
tileOptions: null,
tileClass: OpenLayers.Tile.Image,
grid: null,
singleTile: false,
ratio: 1.5,
buffer: 0,
transitionEffect: 'resize',
numLoadingTiles: 0,
serverResolutions: null,
loading: false,
backBuffer: null,
gridResolution: null,
backBufferResolution: null,
backBufferLonLat: null,
backBufferTimerId: null,
removeBackBufferDelay: null,
className: null,
gridLayout: null,
rowSign: null,
transitionendEvents: [
'transitionend',
'webkitTransitionEnd',
'otransitionend',
'oTransitionEnd'
],
initialize: function (c, b, d, a) {
OpenLayers.Layer.HTTPRequest.prototype.initialize.apply(this, arguments);
this.grid = [
];
this._removeBackBuffer = OpenLayers.Function.bind(this.removeBackBuffer, this);
this.initProperties();
this.rowSign = this.tileOriginCorner.substr(0, 1) === 't' ? 1 : - 1
},
initProperties: function () {
if (this.options.removeBackBufferDelay === undefined) {
  this.removeBackBufferDelay = this.singleTile ? 0 : 2500
}
if (this.options.className === undefined) {
  this.className = this.singleTile ? 'olLayerGridSingleTile' : 'olLayerGrid'
}
},
setMap: function (a) {
OpenLayers.Layer.HTTPRequest.prototype.setMap.call(this, a);
OpenLayers.Element.addClass(this.div, this.className)
},
removeMap: function (a) {
this.removeBackBuffer()
},
destroy: function () {
this.removeBackBuffer();
this.clearGrid();
this.grid = null;
this.tileSize = null;
OpenLayers.Layer.HTTPRequest.prototype.destroy.apply(this, arguments)
},
clearGrid: function () {
if (this.grid) {
  for (var f = 0, b = this.grid.length; f < b; f++) {
    var e = this.grid[f];
    for (var c = 0, a = e.length; c < a; c++) {
      var d = e[c];
      this.destroyTile(d)
    }
  }
  this.grid = [
  ];
  this.gridResolution = null;
  this.gridLayout = null
}
},
addOptions: function (b, a) {
var c = b.singleTile !== undefined && b.singleTile !== this.singleTile;
OpenLayers.Layer.HTTPRequest.prototype.addOptions.apply(this, arguments);
if (this.map && c) {
  this.initProperties();
  this.clearGrid();
  this.tileSize = this.options.tileSize;
  this.setTileSize();
  this.moveTo(null, true)
}
},
clone: function (a) {
if (a == null) {
  a = new OpenLayers.Layer.Grid(this.name, this.url, this.params, this.getOptions())
}
a = OpenLayers.Layer.HTTPRequest.prototype.clone.apply(this, [
  a
]);
if (this.tileSize != null) {
  a.tileSize = this.tileSize.clone()
}
a.grid = [
];
a.gridResolution = null;
a.backBuffer = null;
a.backBufferTimerId = null;
a.loading = false;
a.numLoadingTiles = 0;
return a
},
moveTo: function (f, b, g) {
OpenLayers.Layer.HTTPRequest.prototype.moveTo.apply(this, arguments);
f = f || this.map.getExtent();
if (f != null) {
  var e = !this.grid.length || b;
  var d = this.getTilesBounds();
  var c = this.map.getResolution();
  var a = this.getServerResolution(c);
  if (this.singleTile) {
    if (e || (!g && !d.containsBounds(f))) {
      if (b && this.transitionEffect !== 'resize') {
        this.removeBackBuffer()
      }
      if (!b || this.transitionEffect === 'resize') {
        this.applyBackBuffer(c)
      }
      this.initSingleTile(f)
    }
  } else {
    e = e || !d.intersectsBounds(f, {
      worldBounds: this.map.baseLayer.wrapDateLine && this.map.getMaxExtent()
    });
    if (e) {
      if (b && (this.transitionEffect === 'resize' || this.gridResolution === c)) {
        this.applyBackBuffer(c)
      }
      this.initGriddedTiles(f)
    } else {
      this.moveGriddedTiles()
    }
  }
}
},
getTileData: function (m) {
var q = null,
n = m.lon,
l = m.lat,
d = this.grid.length;
if (this.map && d) {
  var r = this.map.getResolution(),
  a = this.tileSize.w,
  k = this.tileSize.h,
  j = this.grid[0][0].bounds,
  e = j.left,
  o = j.top;
  if (n < e) {
    if (this.map.baseLayer.wrapDateLine) {
      var b = this.map.getMaxExtent().getWidth();
      var c = Math.ceil((e - n) / b);
      n += b * c
    }
  }
  var h = (n - e) / (r * a);
  var f = (o - l) / (r * k);
  var g = Math.floor(h);
  var i = Math.floor(f);
  if (i >= 0 && i < d) {
    var p = this.grid[i][g];
    if (p) {
      q = {
        tile: p,
        i: Math.floor((h - g) * a),
        j: Math.floor((f - i) * k)
      }
    }
  }
}
return q
},
destroyTile: function (a) {
this.removeTileMonitoringHooks(a);
a.destroy()
},
getServerResolution: function (c) {
var f = Number.POSITIVE_INFINITY;
c = c || this.map.getResolution();
if (this.serverResolutions && OpenLayers.Util.indexOf(this.serverResolutions, c) === - 1) {
  var d,
  b,
  e,
  a;
  for (d = this.serverResolutions.length - 1; d >= 0; d--) {
    e = this.serverResolutions[d];
    b = Math.abs(e - c);
    if (b > f) {
      break
    }
    f = b;
    a = e
  }
  c = a
}
return c
},
getServerZoom: function () {
var a = this.getServerResolution();
return this.serverResolutions ? OpenLayers.Util.indexOf(this.serverResolutions, a)  : this.map.getZoomForResolution(a) + (this.zoomOffset || 0)
},
applyBackBuffer: function (b) {
if (this.backBufferTimerId !== null) {
  this.removeBackBuffer()
}
var k = this.backBuffer;
if (!k) {
  k = this.createBackBuffer();
  if (!k) {
    return
  }
  if (b === this.gridResolution) {
    this.div.insertBefore(k, this.div.firstChild)
  } else {
    this.map.baseLayer.div.parentNode.insertBefore(k, this.map.baseLayer.div)
  }
  this.backBuffer = k;
  var d = this.grid[0][0].bounds;
  this.backBufferLonLat = {
    lon: d.left,
    lat: d.top
  };
  this.backBufferResolution = this.gridResolution
}
var h = this.backBufferResolution / b;
var j = k.childNodes,
g;
for (var c = j.length - 1; c >= 0; --c) {
  g = j[c];
  g.style.top = ((h * g._i * g._h) | 0) + 'px';
  g.style.left = ((h * g._j * g._w) | 0) + 'px';
  g.style.width = Math.round(h * g._w) + 'px';
  g.style.height = Math.round(h * g._h) + 'px'
}
var f = this.getViewPortPxFromLonLat(this.backBufferLonLat, b);
var a = this.map.layerContainerOriginPx.x;
var e = this.map.layerContainerOriginPx.y;
k.style.left = Math.round(f.x - a) + 'px';
k.style.top = Math.round(f.y - e) + 'px'
},
createBackBuffer: function () {
var d;
if (this.grid.length > 0) {
  d = document.createElement('div');
  d.id = this.div.id + '_bb';
  d.className = 'olBackBuffer';
  d.style.position = 'absolute';
  var h = this.map;
  d.style.zIndex = this.transitionEffect === 'resize' ? this.getZIndex() - 1 : h.Z_INDEX_BASE.BaseLayer - (h.getNumLayers() - h.getLayerIndex(this));
  for (var f = 0, b = this.grid.length;
  f < b; f++) {
    for (var e = 0, a = this.grid[f].length; e < a; e++) {
      var g = this.grid[f][e],
      c = this.grid[f][e].createBackBuffer();
      if (c) {
        c._i = f;
        c._j = e;
        c._w = g.size.w;
        c._h = g.size.h;
        c.id = g.id + '_bb';
        d.appendChild(c)
      }
    }
  }
}
return d
},
removeBackBuffer: function () {
if (this._transitionElement) {
  for (var a = this.transitionendEvents.length - 1;
  a >= 0; --a) {
    OpenLayers.Event.stopObserving(this._transitionElement, this.transitionendEvents[a], this._removeBackBuffer)
  }
  delete this._transitionElement
}
if (this.backBuffer) {
  if (this.backBuffer.parentNode) {
    this.backBuffer.parentNode.removeChild(this.backBuffer)
  }
  this.backBuffer = null;
  this.backBufferResolution = null;
  if (this.backBufferTimerId !== null) {
    window.clearTimeout(this.backBufferTimerId);
    this.backBufferTimerId = null
  }
}
},
moveByPx: function (b, a) {
if (!this.singleTile) {
  this.moveGriddedTiles()
}
},
setTileSize: function (a) {
if (this.singleTile) {
  a = this.map.getSize();
  a.h = parseInt(a.h * this.ratio, 10);
  a.w = parseInt(a.w * this.ratio, 10)
}
OpenLayers.Layer.HTTPRequest.prototype.setTileSize.apply(this, [
  a
])
},
getTilesBounds: function () {
var d = null;
var c = this.grid.length;
if (c) {
  var e = this.grid[c - 1][0].bounds,
  b = this.grid[0].length * e.getWidth(),
  a = this.grid.length * e.getHeight();
  d = new OpenLayers.Bounds(e.left, e.bottom, e.left + b, e.bottom + a)
}
return d
},
initSingleTile: function (e) {
this.events.triggerEvent('retile');
var a = e.getCenterLonLat();
var g = e.getWidth() * this.ratio;
var b = e.getHeight() * this.ratio;
var f = new OpenLayers.Bounds(a.lon - (g / 2), a.lat - (b / 2), a.lon + (g / 2), a.lat + (b / 2));
var c = this.map.getLayerPxFromLonLat({
  lon: f.left,
  lat: f.top
});
if (!this.grid.length) {
  this.grid[0] = [
  ]
}
var d = this.grid[0][0];
if (!d) {
  d = this.addTile(f, c);
  this.addTileMonitoringHooks(d);
  d.draw();
  this.grid[0][0] = d
} else {
  d.moveTo(f, c)
}
this.removeExcessTiles(1, 1);
this.gridResolution = this.getServerResolution()
},
calculateGridLayout: function (a, j, e) {
var h = e * this.tileSize.w;
var d = e * this.tileSize.h;
var g = a.left - j.lon;
var i = Math.floor(g / h) - this.buffer;
var c = this.rowSign;
var b = c * (j.lat - a.top + d);
var f = Math[~c ? 'floor' : 'ceil'](b / d) - this.buffer * c;
return {
  tilelon: h,
  tilelat: d,
  startcol: i,
  startrow: f
}
},
getTileOrigin: function () {
var b = this.tileOrigin;
if (!b) {
  var c = this.getMaxExtent();
  var a = ({
    tl: [
      'left',
      'top'
    ],
    tr: [
      'right',
      'top'
    ],
    bl: [
      'left',
      'bottom'
    ],
    br: [
      'right',
      'bottom'
    ]
  }) [this.tileOriginCorner];
  b = new OpenLayers.LonLat(c[a[0]], c[a[1]])
}
return b
},
getTileBoundsForGridIndex: function (i, e) {
var h = this.getTileOrigin();
var d = this.gridLayout;
var f = d.tilelon;
var c = d.tilelat;
var a = d.startcol;
var g = d.startrow;
var b = this.rowSign;
return new OpenLayers.Bounds(h.lon + (a + e) * f, h.lat - (g + i * b) * c * b, h.lon + (a + e + 1) * f, h.lat - (g + (i - 1) * b) * c * b)
},
initGriddedTiles: function (g) {
this.events.triggerEvent('retile');
var e = this.map.getSize();
var A = this.getTileOrigin();
var q = this.map.getResolution(),
o = this.getServerResolution(),
h = q / o,
m = {
  w: this.tileSize.w / h,
  h: this.tileSize.h / h
};
var v = Math.ceil(e.h / m.h) + 2 * this.buffer + 1;
var w = Math.ceil(e.w / m.w) + 2 * this.buffer + 1;
var p = this.calculateGridLayout(g, A, o);
this.gridLayout = p;
var d = p.tilelon;
var j = p.tilelat;
var a = this.map.layerContainerOriginPx.x;
var s = this.map.layerContainerOriginPx.y;
var b = this.getTileBoundsForGridIndex(0, 0);
var k = this.map.getViewPortPxFromLonLat(new OpenLayers.LonLat(b.left, b.top));
k.x = Math.round(k.x) - a;
k.y = Math.round(k.y) - s;
var x = [
],
y = this.map.getCenter();
var u = 0;
do {
  var f = this.grid[u];
  if (!f) {
    f = [
    ];
    this.grid.push(f)
  }
  var c = 0;
  do {
    b = this.getTileBoundsForGridIndex(u, c);
    var n = k.clone();
    n.x = n.x + c * Math.round(m.w);
    n.y = n.y + u * Math.round(m.h);
    var z = f[c];
    if (!z) {
      z = this.addTile(b, n);
      this.addTileMonitoringHooks(z);
      f.push(z)
    } else {
      z.moveTo(b, n, false)
    }
    var r = b.getCenterLonLat();
    x.push({
      tile: z,
      distance: Math.pow(r.lon - y.lon, 2) + Math.pow(r.lat - y.lat, 2)
    });
    c += 1
  } while ((b.right <= g.right + d * this.buffer) || c < w);
  u += 1
} while ((b.bottom >= g.bottom - j * this.buffer) || u < v);
this.removeExcessTiles(u, c);
var q = this.getServerResolution();
this.gridResolution = q;
x.sort(function (B, i) {
  return B.distance - i.distance
});
for (var t = 0, l = x.length; t < l; ++t) {
  x[t].tile.draw()
}
},
getMaxExtent: function () {
return this.maxExtent
},
addTile: function (c, a) {
var b = new this.tileClass(this, a, c, null, this.tileSize, this.tileOptions);
this.events.triggerEvent('addtile', {
  tile: b
});
return b
},
addTileMonitoringHooks: function (b) {
var a = 'olTileReplacing';
b.onLoadStart = function () {
  if (this.loading === false) {
    this.loading = true;
    this.events.triggerEvent('loadstart')
  }
  this.events.triggerEvent('tileloadstart', {
    tile: b
  });
  this.numLoadingTiles++;
  if (!this.singleTile && this.backBuffer && this.gridResolution === this.backBufferResolution) {
    OpenLayers.Element.addClass(b.getTile(), a)
  }
};
b.onLoadEnd = function (e) {
  this.numLoadingTiles--;
  var h = e.type === 'unload';
  this.events.triggerEvent('tileloaded', {
    tile: b,
    aborted: h
  });
  if (!this.singleTile && !h && this.backBuffer && this.gridResolution === this.backBufferResolution) {
    var g = b.getTile();
    if (OpenLayers.Element.getStyle(g, 'display') === 'none') {
      var d = document.getElementById(b.id + '_bb');
      if (d) {
        d.parentNode.removeChild(d)
      }
    }
    OpenLayers.Element.removeClass(g, a)
  }
  if (this.numLoadingTiles === 0) {
    if (this.backBuffer) {
      if (this.backBuffer.childNodes.length === 0) {
        this.removeBackBuffer()
      } else {
        this._transitionElement = h ? this.div.lastChild : b.imgDiv;
        var c = this.transitionendEvents;
        for (var f = c.length - 1; f >= 0;
        --f) {
          OpenLayers.Event.observe(this._transitionElement, c[f], this._removeBackBuffer)
        }
        this.backBufferTimerId = window.setTimeout(this._removeBackBuffer, this.removeBackBufferDelay)
      }
    }
    this.loading = false;
    this.events.triggerEvent('loadend')
  }
};
b.onLoadError = function () {
  this.events.triggerEvent('tileerror', {
    tile: b
  })
};
b.events.on({
  loadstart: b.onLoadStart,
  loadend: b.onLoadEnd,
  unload: b.onLoadEnd,
  loaderror: b.onLoadError,
  scope: this
})
},
removeTileMonitoringHooks: function (a) {
a.unload();
a.events.un({
  loadstart: a.onLoadStart,
  loadend: a.onLoadEnd,
  unload: a.onLoadEnd,
  loaderror: a.onLoadError,
  scope: this
})
},
moveGriddedTiles: function () {
var a = this.buffer + 1;
while (true) {
  var e = this.grid[0][0];
  var d = {
    x: e.position.x + this.map.layerContainerOriginPx.x,
    y: e.position.y + this.map.layerContainerOriginPx.y
  };
  var b = this.getServerResolution() / this.map.getResolution();
  var c = {
    w: Math.round(this.tileSize.w * b),
    h: Math.round(this.tileSize.h * b)
  };
  if (d.x > - c.w * (a - 1)) {
    this.shiftColumn(true, c)
  } else {
    if (d.x < - c.w * a) {
      this.shiftColumn(false, c)
    } else {
      if (d.y > - c.h * (a - 1)) {
        this.shiftRow(true, c)
      } else {
        if (d.y < - c.h * a) {
          this.shiftRow(false, c)
        } else {
          break
        }
      }
    }
  }
}
},
shiftRow: function (n, l) {
var a = this.grid;
var k = n ? 0 : (a.length - 1);
var d = n ? - 1 : 1;
var b = this.rowSign;
var c = this.gridLayout;
c.startrow += d * b;
var e = a[k];
var m = a[n ? 'pop' : 'shift']();
for (var f = 0, h = m.length; f < h; f++) {
  var j = m[f];
  var g = e[f].position.clone();
  g.y += l.h * d;
  j.moveTo(this.getTileBoundsForGridIndex(k, f), g)
}
a[n ? 'unshift' : 'push'](m)
},
shiftColumn: function (l, j) {
var a = this.grid;
var h = l ? 0 : (a[0].length - 1);
var c = l ? - 1 : 1;
var b = this.gridLayout;
b.startcol += c;
for (var d = 0, f = a.length; d < f; d++) {
  var k = a[d];
  var e = k[h].position.clone();
  var g = k[l ? 'pop' : 'shift']();
  e.x += j.w * c;
  g.moveTo(this.getTileBoundsForGridIndex(d, h), e);
  k[l ? 'unshift' : 'push'](g)
}
},
removeExcessTiles: function (e, c) {
var b,
a;
while (this.grid.length > e) {
  var f = this.grid.pop();
  for (b = 0, a = f.length; b < a; b++) {
    var d = f[b];
    this.destroyTile(d)
  }
}
for (b = 0, a = this.grid.length;
b < a; b++) {
  while (this.grid[b].length > c) {
    var f = this.grid[b];
    var d = f.pop();
    this.destroyTile(d)
  }
}
},
onMapResize: function () {
if (this.singleTile) {
  this.clearGrid();
  this.setTileSize()
}
},
getTileBounds: function (d) {
var c = this.maxExtent;
var f = this.getResolution();
var e = f * this.tileSize.w;
var b = f * this.tileSize.h;
var h = this.getLonLatFromViewPortPx(d);
var a = c.left + (e * Math.floor((h.lon - c.left) / e));
var g = c.bottom + (b * Math.floor((h.lat - c.bottom) / b));
return new OpenLayers.Bounds(a, g, a + e, g + b)
},
CLASS_NAME: 'OpenLayers.Layer.Grid'
});
OpenLayers.Layer.Markers = OpenLayers.Class(OpenLayers.Layer, {
isBaseLayer: false,
markers: null,
drawn: false,
initialize: function (b, a) {
OpenLayers.Layer.prototype.initialize.apply(this, arguments);
this.markers = [
]
},
destroy: function () {
this.clearMarkers();
this.markers = null;
OpenLayers.Layer.prototype.destroy.apply(this, arguments)
},
setOpacity: function (b) {
if (b != this.opacity) {
  this.opacity = b;
  for (var c = 0, a = this.markers.length; c < a; c++) {
    this.markers[c].setOpacity(this.opacity)
  }
}
},
moveTo: function (d, b, e) {
OpenLayers.Layer.prototype.moveTo.apply(this, arguments);
if (b || !this.drawn) {
  for (var c = 0, a = this.markers.length; c < a; c++) {
    this.drawMarker(this.markers[c])
  }
  this.drawn = true
}
},
addMarker: function (a) {
this.markers.push(a);
if (this.opacity < 1) {
  a.setOpacity(this.opacity)
}
if (this.map && this.map.getExtent()) {
  a.map = this.map;
  this.drawMarker(a)
}
},
removeMarker: function (a) {
if (this.markers && this.markers.length) {
  OpenLayers.Util.removeItem(this.markers, a);
  a.erase()
}
},
clearMarkers: function () {
if (this.markers != null) {
  while (this.markers.length > 0) {
    this.removeMarker(this.markers[0])
  }
}
},
drawMarker: function (a) {
var b = this.map.getLayerPxFromLonLat(a.lonlat);
if (b == null) {
  a.display(false)
} else {
  if (!a.isDrawn()) {
    var c = a.draw(b);
    this.div.appendChild(c)
  } else {
    if (a.icon) {
      a.icon.moveTo(b)
    }
  }
}
},
getDataExtent: function () {
var b = null;
if (this.markers && (this.markers.length > 0)) {
  var b = new OpenLayers.Bounds();
  for (var d = 0, a = this.markers.length; d < a; d++) {
    var c = this.markers[d];
    b.extend(c.lonlat)
  }
}
return b
},
CLASS_NAME: 'OpenLayers.Layer.Markers'
});
OpenLayers.Control.DrawFeature = OpenLayers.Class(OpenLayers.Control, {
layer: null,
callbacks: null,
multi: false,
featureAdded: function () {
},
initialize: function (b, c, a) {
OpenLayers.Control.prototype.initialize.apply(this, [
  a
]);
this.callbacks = OpenLayers.Util.extend({
  done: this.drawFeature,
  modify: function (f, e) {
    this.layer.events.triggerEvent('sketchmodified', {
      vertex: f,
      feature: e
    })
  },
  create: function (f, e) {
    this.layer.events.triggerEvent('sketchstarted', {
      vertex: f,
      feature: e
    })
  }
}, this.callbacks);
this.layer = b;
this.handlerOptions = this.handlerOptions || {
};
this.handlerOptions.layerOptions = OpenLayers.Util.applyDefaults(this.handlerOptions.layerOptions, {
  renderers: b.renderers,
  rendererOptions: b.rendererOptions
});
if (!('multi' in this.handlerOptions)) {
  this.handlerOptions.multi = this.multi
}
var d = this.layer.styleMap && this.layer.styleMap.styles.temporary;
if (d) {
  this.handlerOptions.layerOptions = OpenLayers.Util.applyDefaults(this.handlerOptions.layerOptions, {
    styleMap: new OpenLayers.StyleMap({
      'default': d
    })
  })
}
this.handler = new c(this, this.callbacks, this.handlerOptions)
},
drawFeature: function (c) {
var a = new OpenLayers.Feature.Vector(c);
var b = this.layer.events.triggerEvent('sketchcomplete', {
  feature: a
});
if (b !== false) {
  a.state = OpenLayers.State.INSERT;
  this.layer.addFeatures([a]);
  this.featureAdded(a);
  this.events.triggerEvent('featureadded', {
    feature: a
  })
}
},
insertXY: function (a, b) {
if (this.handler && this.handler.line) {
  this.handler.insertXY(a, b)
}
},
insertDeltaXY: function (b, a) {
if (this.handler && this.handler.line) {
  this.handler.insertDeltaXY(b, a)
}
},
insertDirectionLength: function (b, a) {
if (this.handler && this.handler.line) {
  this.handler.insertDirectionLength(b, a)
}
},
insertDeflectionLength: function (b, a) {
if (this.handler && this.handler.line) {
  this.handler.insertDeflectionLength(b, a)
}
},
undo: function () {
return this.handler.undo && this.handler.undo()
},
redo: function () {
return this.handler.redo && this.handler.redo()
},
finishSketch: function () {
this.handler.finishGeometry()
},
cancel: function () {
this.handler.cancel()
},
CLASS_NAME: 'OpenLayers.Control.DrawFeature'
});
OpenLayers.Control.Measure = OpenLayers.Class(OpenLayers.Control, {
callbacks: null,
displaySystem: 'metric',
geodesic: false,
displaySystemUnits: {
geographic: [
  'dd'
],
english: [
  'mi',
  'ft',
  'in'
],
metric: [
  'km',
  'm'
]
},
partialDelay: 300,
delayedTrigger: null,
persist: false,
immediate: false,
initialize: function (b, a) {
OpenLayers.Control.prototype.initialize.apply(this, [
  a
]);
var c = {
  done: this.measureComplete,
  point: this.measurePartial
};
if (this.immediate) {
  c.modify = this.measureImmediate
}
this.callbacks = OpenLayers.Util.extend(c, this.callbacks);
this.handlerOptions = OpenLayers.Util.extend({
  persist: this.persist
}, this.handlerOptions);
this.handler = new b(this, this.callbacks, this.handlerOptions)
},
deactivate: function () {
this.cancelDelay();
return OpenLayers.Control.prototype.deactivate.apply(this, arguments)
},
cancel: function () {
this.cancelDelay();
this.handler.cancel()
},
setImmediate: function (a) {
this.immediate = a;
if (this.immediate) {
  this.callbacks.modify = this.measureImmediate
} else {
  delete this.callbacks.modify
}
},
updateHandler: function (b, a) {
var c = this.active;
if (c) {
  this.deactivate()
}
this.handler = new b(this, this.callbacks, a);
if (c) {
  this.activate()
}
},
measureComplete: function (a) {
this.cancelDelay();
this.measure(a, 'measure')
},
measurePartial: function (a, b) {
this.cancelDelay();
b = b.clone();
if (this.handler.freehandMode(this.handler.evt)) {
  this.measure(b, 'measurepartial')
} else {
  this.delayedTrigger = window.setTimeout(OpenLayers.Function.bind(function () {
    this.delayedTrigger = null;
    this.measure(b, 'measurepartial')
  }, this), this.partialDelay)
}
},
measureImmediate: function (a, c, b) {
if (b && !this.handler.freehandMode(this.handler.evt)) {
  this.cancelDelay();
  this.measure(c.geometry, 'measurepartial')
}
},
cancelDelay: function () {
if (this.delayedTrigger !== null) {
  window.clearTimeout(this.delayedTrigger);
  this.delayedTrigger = null
}
},
measure: function (d, b) {
var c,
a;
if (d.CLASS_NAME.indexOf('LineString') > - 1) {
  c = this.getBestLength(d);
  a = 1
} else {
  c = this.getBestArea(d);
  a = 2
}
this.events.triggerEvent(b, {
  measure: c[0],
  units: c[1],
  order: a,
  geometry: d
})
},
getBestArea: function (f) {
var b = this.displaySystemUnits[this.displaySystem];
var e,
d;
for (var c = 0, a = b.length; c < a; ++c) {
  e = b[c];
  d = this.getArea(f, e);
  if (d > 1) {
    break
  }
}
return [d,
e]
},
getArea: function (f, a) {
var b,
c;
if (this.geodesic) {
  b = f.getGeodesicArea(this.map.getProjectionObject());
  c = 'm'
} else {
  b = f.getArea();
  c = this.map.getUnits()
}
var e = OpenLayers.INCHES_PER_UNIT[a];
if (e) {
  var d = OpenLayers.INCHES_PER_UNIT[c];
  b *= Math.pow((d / e), 2)
}
return b
},
getBestLength: function (f) {
var b = this.displaySystemUnits[this.displaySystem];
var e,
d;
for (var c = 0, a = b.length; c < a; ++c) {
  e = b[c];
  d = this.getLength(f, e);
  if (d > 1) {
    break
  }
}
return [d,
e]
},
getLength: function (f, a) {
var b,
c;
if (this.geodesic) {
  b = f.getGeodesicLength(this.map.getProjectionObject());
  c = 'm'
} else {
  b = f.getLength();
  c = this.map.getUnits()
}
var e = OpenLayers.INCHES_PER_UNIT[a];
if (e) {
  var d = OpenLayers.INCHES_PER_UNIT[c];
  b *= (d / e)
}
return b
},
CLASS_NAME: 'OpenLayers.Control.Measure'
});
OpenLayers.Handler.Point = OpenLayers.Class(OpenLayers.Handler, {
point: null,
layer: null,
multi: false,
citeCompliant: false,
mouseDown: false,
stoppedDown: null,
lastDown: null,
lastUp: null,
persist: false,
stopDown: false,
stopUp: false,
layerOptions: null,
pixelTolerance: 5,
lastTouchPx: null,
initialize: function (c, b, a) {
if (!(a && a.layerOptions && a.layerOptions.styleMap)) {
  this.style = OpenLayers.Util.extend(OpenLayers.Feature.Vector.style['default'], {
  })
}
OpenLayers.Handler.prototype.initialize.apply(this, arguments)
},
activate: function () {
if (!OpenLayers.Handler.prototype.activate.apply(this, arguments)) {
  return false
}
var a = OpenLayers.Util.extend({
  displayInLayerSwitcher: false,
  calculateInRange: OpenLayers.Function.True,
  wrapDateLine: this.citeCompliant
}, this.layerOptions);
this.layer = new OpenLayers.Layer.Vector(this.CLASS_NAME, a);
this.map.addLayer(this.layer);
return true
},
createFeature: function (a) {
var b = this.layer.getLonLatFromViewPortPx(a);
var c = new OpenLayers.Geometry.Point(b.lon, b.lat);
this.point = new OpenLayers.Feature.Vector(c);
this.callback('create', [
  this.point.geometry,
  this.point
]);
this.point.geometry.clearBounds();
this.layer.addFeatures([this.point], {
  silent: true
})
},
deactivate: function () {
if (!OpenLayers.Handler.prototype.deactivate.apply(this, arguments)) {
  return false
}
this.cancel();
if (this.layer.map != null) {
  this.destroyFeature(true);
  this.layer.destroy(false)
}
this.layer = null;
return true
},
destroyFeature: function (a) {
if (this.layer && (a || !this.persist)) {
  this.layer.destroyFeatures()
}
this.point = null
},
destroyPersistedFeature: function () {
var a = this.layer;
if (a && a.features.length > 1) {
  this.layer.features[0].destroy()
}
},
finalize: function (b) {
var a = b ? 'cancel' : 'done';
this.mouseDown = false;
this.lastDown = null;
this.lastUp = null;
this.lastTouchPx = null;
this.callback(a, [
  this.geometryClone()
]);
this.destroyFeature(b)
},
cancel: function () {
this.finalize(true)
},
click: function (a) {
OpenLayers.Event.stop(a);
return false
},
dblclick: function (a) {
OpenLayers.Event.stop(a);
return false
},
modifyFeature: function (a) {
if (!this.point) {
  this.createFeature(a)
}
var b = this.layer.getLonLatFromViewPortPx(a);
this.point.geometry.x = b.lon;
this.point.geometry.y = b.lat;
this.callback('modify', [
  this.point.geometry,
  this.point,
  false
]);
this.point.geometry.clearBounds();
this.drawFeature()
},
drawFeature: function () {
this.layer.drawFeature(this.point, this.style)
},
getGeometry: function () {
var a = this.point && this.point.geometry;
if (a && this.multi) {
  a = new OpenLayers.Geometry.MultiPoint([a])
}
return a
},
geometryClone: function () {
var a = this.getGeometry();
return a && a.clone()
},
mousedown: function (a) {
return this.down(a)
},
touchstart: function (a) {
this.startTouch();
this.lastTouchPx = a.xy;
return this.down(a)
},
mousemove: function (a) {
return this.move(a)
},
touchmove: function (a) {
this.lastTouchPx = a.xy;
return this.move(a)
},
mouseup: function (a) {
return this.up(a)
},
touchend: function (a) {
a.xy = this.lastTouchPx;
return this.up(a)
},
down: function (a) {
this.mouseDown = true;
this.lastDown = a.xy;
if (!this.touch) {
  this.modifyFeature(a.xy)
}
this.stoppedDown = this.stopDown;
return !this.stopDown
},
move: function (a) {
if (!this.touch && (!this.mouseDown || this.stoppedDown)) {
  this.modifyFeature(a.xy)
}
return true
},
up: function (a) {
this.mouseDown = false;
this.stoppedDown = this.stopDown;
if (!this.checkModifiers(a)) {
  return true
}
if (this.lastUp && this.lastUp.equals(a.xy)) {
  return true
}
if (this.lastDown && this.passesTolerance(this.lastDown, a.xy, this.pixelTolerance)) {
  if (this.touch) {
    this.modifyFeature(a.xy)
  }
  if (this.persist) {
    this.destroyPersistedFeature()
  }
  this.lastUp = a.xy;
  this.finalize();
  return !this.stopUp
} else {
  return true
}
},
mouseout: function (a) {
if (OpenLayers.Util.mouseLeft(a, this.map.viewPortDiv)) {
  this.stoppedDown = this.stopDown;
  this.mouseDown = false
}
},
passesTolerance: function (e, d, a) {
var b = true;
if (a != null && e && d) {
  var c = e.distanceTo(d);
  if (c > a) {
    b = false
  }
}
return b
},
CLASS_NAME: 'OpenLayers.Handler.Point'
});
OpenLayers.Handler.Path = OpenLayers.Class(OpenLayers.Handler.Point, {
line: null,
maxVertices: null,
doubleTouchTolerance: 20,
freehand: false,
freehandToggle: 'shiftKey',
timerId: null,
redoStack: null,
createFeature: function (a) {
var b = this.layer.getLonLatFromViewPortPx(a);
var c = new OpenLayers.Geometry.Point(b.lon, b.lat);
this.point = new OpenLayers.Feature.Vector(c);
this.line = new OpenLayers.Feature.Vector(new OpenLayers.Geometry.LineString([this.point.geometry]));
this.callback('create', [
  this.point.geometry,
  this.getSketch()
]);
this.point.geometry.clearBounds();
this.layer.addFeatures([this.line,
this.point], {
  silent: true
})
},
destroyFeature: function (a) {
OpenLayers.Handler.Point.prototype.destroyFeature.call(this, a);
this.line = null
},
destroyPersistedFeature: function () {
var a = this.layer;
if (a && a.features.length > 2) {
  this.layer.features[0].destroy()
}
},
removePoint: function () {
if (this.point) {
  this.layer.removeFeatures([this.point])
}
},
addPoint: function (a) {
this.layer.removeFeatures([this.point]);
var b = this.layer.getLonLatFromViewPortPx(a);
this.point = new OpenLayers.Feature.Vector(new OpenLayers.Geometry.Point(b.lon, b.lat));
this.line.geometry.addComponent(this.point.geometry, this.line.geometry.components.length);
this.layer.addFeatures([this.point]);
this.callback('point', [
  this.point.geometry,
  this.getGeometry()
]);
this.callback('modify', [
  this.point.geometry,
  this.getSketch()
]);
this.drawFeature();
delete this.redoStack
},
insertXY: function (a, b) {
this.line.geometry.addComponent(new OpenLayers.Geometry.Point(a, b), this.getCurrentPointIndex());
this.drawFeature();
delete this.redoStack
},
insertDeltaXY: function (b, a) {
var c = this.getCurrentPointIndex() - 1;
var d = this.line.geometry.components[c];
if (d && !isNaN(d.x) && !isNaN(d.y)) {
  this.insertXY(d.x + b, d.y + a)
}
},
insertDirectionLength: function (d, c) {
d *= Math.PI / 180;
var b = c * Math.cos(d);
var a = c * Math.sin(d);
this.insertDeltaXY(b, a)
},
insertDeflectionLength: function (c, b) {
var d = this.getCurrentPointIndex() - 1;
if (d > 0) {
  var e = this.line.geometry.components[d];
  var f = this.line.geometry.components[d - 1];
  var a = Math.atan2(e.y - f.y, e.x - f.x);
  this.insertDirectionLength((a * 180 / Math.PI) + c, b)
}
},
getCurrentPointIndex: function () {
return this.line.geometry.components.length - 1
},
undo: function () {
var h = this.line.geometry;
var e = h.components;
var b = this.getCurrentPointIndex() - 1;
var g = e[b];
var f = h.removeComponent(g);
if (f) {
  if (this.touch && b > 0) {
    e = h.components;
    var d = e[b - 1];
    var a = this.getCurrentPointIndex();
    var c = e[a];
    c.x = d.x;
    c.y = d.y
  }
  if (!this.redoStack) {
    this.redoStack = [
    ]
  }
  this.redoStack.push(g);
  this.drawFeature()
}
return f
},
redo: function () {
var a = this.redoStack && this.redoStack.pop();
if (a) {
  this.line.geometry.addComponent(a, this.getCurrentPointIndex());
  this.drawFeature()
}
return !!a
},
freehandMode: function (a) {
return (this.freehandToggle && a[this.freehandToggle]) ? !this.freehand : this.freehand
},
modifyFeature: function (b, a) {
if (!this.line) {
  this.createFeature(b)
}
var c = this.layer.getLonLatFromViewPortPx(b);
this.point.geometry.x = c.lon;
this.point.geometry.y = c.lat;
this.callback('modify', [
  this.point.geometry,
  this.getSketch(),
  a
]);
this.point.geometry.clearBounds();
this.drawFeature()
},
drawFeature: function () {
this.layer.drawFeature(this.line, this.style);
this.layer.drawFeature(this.point, this.style)
},
getSketch: function () {
return this.line
},
getGeometry: function () {
var a = this.line && this.line.geometry;
if (a && this.multi) {
  a = new OpenLayers.Geometry.MultiLineString([a])
}
return a
},
touchstart: function (a) {
if (this.timerId && this.passesTolerance(this.lastTouchPx, a.xy, this.doubleTouchTolerance)) {
  this.finishGeometry();
  window.clearTimeout(this.timerId);
  this.timerId = null;
  return false
} else {
  if (this.timerId) {
    window.clearTimeout(this.timerId);
    this.timerId = null
  }
  this.timerId = window.setTimeout(OpenLayers.Function.bind(function () {
    this.timerId = null
  }, this), 300);
  return OpenLayers.Handler.Point.prototype.touchstart.call(this, a)
}
},
down: function (a) {
var b = this.stopDown;
if (this.freehandMode(a)) {
  b = true;
  if (this.touch) {
    this.modifyFeature(a.xy, !!this.lastUp);
    OpenLayers.Event.stop(a)
  }
}
if (!this.touch && (!this.lastDown || !this.passesTolerance(this.lastDown, a.xy, this.pixelTolerance))) {
  this.modifyFeature(a.xy, !!this.lastUp)
}
this.mouseDown = true;
this.lastDown = a.xy;
this.stoppedDown = b;
return !b
},
move: function (a) {
if (this.stoppedDown && this.freehandMode(a)) {
  if (this.persist) {
    this.destroyPersistedFeature()
  }
  if (this.maxVertices && this.line && this.line.geometry.components.length === this.maxVertices) {
    this.removePoint();
    this.finalize()
  } else {
    this.addPoint(a.xy)
  }
  return false
}
if (!this.touch && (!this.mouseDown || this.stoppedDown)) {
  this.modifyFeature(a.xy, !!this.lastUp)
}
return true
},
up: function (a) {
if (this.mouseDown && (!this.lastUp || !this.lastUp.equals(a.xy))) {
  if (this.stoppedDown && this.freehandMode(a)) {
    if (this.persist) {
      this.destroyPersistedFeature()
    }
    this.removePoint();
    this.finalize()
  } else {
    if (this.passesTolerance(this.lastDown, a.xy, this.pixelTolerance)) {
      if (this.touch) {
        this.modifyFeature(a.xy)
      }
      if (this.lastUp == null && this.persist) {
        this.destroyPersistedFeature()
      }
      this.addPoint(a.xy);
      this.lastUp = a.xy;
      if (this.line.geometry.components.length === this.maxVertices + 1) {
        this.finishGeometry()
      }
    }
  }
}
this.stoppedDown = this.stopDown;
this.mouseDown = false;
return !this.stopUp
},
finishGeometry: function () {
var a = this.line.geometry.components.length - 1;
this.line.geometry.removeComponent(this.line.geometry.components[a]);
this.removePoint();
this.finalize()
},
dblclick: function (a) {
if (!this.freehandMode(a)) {
  this.finishGeometry()
}
return false
},
CLASS_NAME: 'OpenLayers.Handler.Path'
});
OpenLayers.Handler.Polygon = OpenLayers.Class(OpenLayers.Handler.Path, {
holeModifier: null,
drawingHole: false,
polygon: null,
createFeature: function (a) {
var b = this.layer.getLonLatFromViewPortPx(a);
var c = new OpenLayers.Geometry.Point(b.lon, b.lat);
this.point = new OpenLayers.Feature.Vector(c);
this.line = new OpenLayers.Feature.Vector(new OpenLayers.Geometry.LinearRing([this.point.geometry]));
this.polygon = new OpenLayers.Feature.Vector(new OpenLayers.Geometry.Polygon([this.line.geometry]));
this.callback('create', [
  this.point.geometry,
  this.getSketch()
]);
this.point.geometry.clearBounds();
this.layer.addFeatures([this.polygon,
this.point], {
  silent: true
})
},
addPoint: function (a) {
if (!this.drawingHole && this.holeModifier && this.evt && this.evt[this.holeModifier]) {
  var f = this.point.geometry;
  var e = this.control.layer.features;
  var d,
  c;
  for (var b = e.length - 1; b >= 0; --b) {
    d = e[b].geometry;
    if ((d instanceof OpenLayers.Geometry.Polygon || d instanceof OpenLayers.Geometry.MultiPolygon) && d.intersects(f)) {
      c = e[b];
      this.control.layer.removeFeatures([c], {
        silent: true
      });
      this.control.layer.events.registerPriority('sketchcomplete', this, this.finalizeInteriorRing);
      this.control.layer.events.registerPriority('sketchmodified', this, this.enforceTopology);
      c.geometry.addComponent(this.line.geometry);
      this.polygon = c;
      this.drawingHole = true;
      break
    }
  }
}
OpenLayers.Handler.Path.prototype.addPoint.apply(this, arguments)
},
getCurrentPointIndex: function () {
return this.line.geometry.components.length - 2
},
enforceTopology: function (d) {
var a = d.vertex;
var c = this.line.geometry.components;
if (!this.polygon.geometry.intersects(a)) {
  var b = c[c.length - 3];
  a.x = b.x;
  a.y = b.y
}
},
finishGeometry: function () {
var a = this.line.geometry.components.length - 2;
this.line.geometry.removeComponent(this.line.geometry.components[a]);
this.removePoint();
this.finalize()
},
finalizeInteriorRing: function () {
var c = this.line.geometry;
var b = (c.getArea() !== 0);
if (b) {
  var h = this.polygon.geometry.components;
  for (var d = h.length - 2; d >= 0; --d) {
    if (c.intersects(h[d])) {
      b = false;
      break
    }
  }
  if (b) {
    var g;
    outer: for (var d = h.length - 2; d > 0; --d) {
      var e = h[d].components;
      for (var a = 0, f = e.length; a < f; ++a) {
        if (c.containsPoint(e[a])) {
          b = false;
          break outer
        }
      }
    }
  }
}
if (b) {
  if (this.polygon.state !== OpenLayers.State.INSERT) {
    this.polygon.state = OpenLayers.State.UPDATE
  }
} else {
  this.polygon.geometry.removeComponent(c)
}
this.restoreFeature();
return false
},
cancel: function () {
if (this.drawingHole) {
  this.polygon.geometry.removeComponent(this.line.geometry);
  this.restoreFeature(true)
}
return OpenLayers.Handler.Path.prototype.cancel.apply(this, arguments)
},
restoreFeature: function (a) {
this.control.layer.events.unregister('sketchcomplete', this, this.finalizeInteriorRing);
this.control.layer.events.unregister('sketchmodified', this, this.enforceTopology);
this.layer.removeFeatures([this.polygon], {
  silent: true
});
this.control.layer.addFeatures([this.polygon], {
  silent: true
});
this.drawingHole = false;
if (!a) {
  this.control.layer.events.triggerEvent('sketchcomplete', {
    feature: this.polygon
  })
}
},
destroyFeature: function (a) {
OpenLayers.Handler.Path.prototype.destroyFeature.call(this, a);
this.polygon = null
},
drawFeature: function () {
this.layer.drawFeature(this.polygon, this.style);
this.layer.drawFeature(this.point, this.style)
},
getSketch: function () {
return this.polygon
},
getGeometry: function () {
var a = this.polygon && this.polygon.geometry;
if (a && this.multi) {
  a = new OpenLayers.Geometry.MultiPolygon([a])
}
return a
},
CLASS_NAME: 'OpenLayers.Handler.Polygon'
});
OpenLayers.Handler.RegularPolygon = OpenLayers.Class(OpenLayers.Handler.Drag, {
sides: 4,
radius: null,
snapAngle: null,
snapToggle: 'shiftKey',
layerOptions: null,
persist: false,
irregular: false,
citeCompliant: false,
angle: null,
fixedRadius: false,
feature: null,
layer: null,
origin: null,
initialize: function (c, b, a) {
if (!(a && a.layerOptions && a.layerOptions.styleMap)) {
  this.style = OpenLayers.Util.extend(OpenLayers.Feature.Vector.style['default'], {
  })
}
OpenLayers.Handler.Drag.prototype.initialize.apply(this, [
  c,
  b,
  a
]);
this.options = (a) ? a : {
}
},
setOptions: function (a) {
OpenLayers.Util.extend(this.options, a);
OpenLayers.Util.extend(this, a)
},
activate: function () {
var a = false;
if (OpenLayers.Handler.Drag.prototype.activate.apply(this, arguments)) {
  var b = OpenLayers.Util.extend({
    displayInLayerSwitcher: false,
    calculateInRange: OpenLayers.Function.True,
    wrapDateLine: this.citeCompliant
  }, this.layerOptions);
  this.layer = new OpenLayers.Layer.Vector(this.CLASS_NAME, b);
  this.map.addLayer(this.layer);
  a = true
}
return a
},
deactivate: function () {
var a = false;
if (OpenLayers.Handler.Drag.prototype.deactivate.apply(this, arguments)) {
  if (this.dragging) {
    this.cancel()
  }
  if (this.layer.map != null) {
    this.layer.destroy(false);
    if (this.feature) {
      this.feature.destroy()
    }
  }
  this.layer = null;
  this.feature = null;
  a = true
}
return a
},
down: function (a) {
this.fixedRadius = !!(this.radius);
var b = this.layer.getLonLatFromViewPortPx(a.xy);
this.origin = new OpenLayers.Geometry.Point(b.lon, b.lat);
if (!this.fixedRadius || this.irregular) {
  this.radius = this.map.getResolution()
}
if (this.persist) {
  this.clear()
}
this.feature = new OpenLayers.Feature.Vector();
this.createGeometry();
this.callback('create', [
  this.origin,
  this.feature
]);
this.layer.addFeatures([this.feature], {
  silent: true
});
this.layer.drawFeature(this.feature, this.style)
},
move: function (c) {
var f = this.layer.getLonLatFromViewPortPx(c.xy);
var a = new OpenLayers.Geometry.Point(f.lon, f.lat);
if (this.irregular) {
  var g = Math.sqrt(2) * Math.abs(a.y - this.origin.y) / 2;
  this.radius = Math.max(this.map.getResolution() / 2, g)
} else {
  if (this.fixedRadius) {
    this.origin = a
  } else {
    this.calculateAngle(a, c);
    this.radius = Math.max(this.map.getResolution() / 2, a.distanceTo(this.origin))
  }
}
this.modifyGeometry();
if (this.irregular) {
  var d = a.x - this.origin.x;
  var b = a.y - this.origin.y;
  var e;
  if (b == 0) {
    e = d / (this.radius * Math.sqrt(2))
  } else {
    e = d / b
  }
  this.feature.geometry.resize(1, this.origin, e);
  this.feature.geometry.move(d / 2, b / 2)
}
this.layer.drawFeature(this.feature, this.style)
},
up: function (a) {
this.finalize();
if (this.start == this.last) {
  this.callback('done', [
    a.xy
  ])
}
},
out: function (a) {
this.finalize()
},
createGeometry: function () {
this.angle = Math.PI * ((1 / this.sides) - (1 / 2));
if (this.snapAngle) {
  this.angle += this.snapAngle * (Math.PI / 180)
}
this.feature.geometry = OpenLayers.Geometry.Polygon.createRegularPolygon(this.origin, this.radius, this.sides, this.snapAngle)
},
modifyGeometry: function () {
var d,
a;
var b = this.feature.geometry.components[0];
if (b.components.length != (this.sides + 1)) {
  this.createGeometry();
  b = this.feature.geometry.components[0]
}
for (var c = 0; c < this.sides; ++c) {
  a = b.components[c];
  d = this.angle + (c * 2 * Math.PI / this.sides);
  a.x = this.origin.x + (this.radius * Math.cos(d));
  a.y = this.origin.y + (this.radius * Math.sin(d));
  a.clearBounds()
}
},
calculateAngle: function (a, b) {
var d = Math.atan2(a.y - this.origin.y, a.x - this.origin.x);
if (this.snapAngle && (this.snapToggle && !b[this.snapToggle])) {
  var c = (Math.PI / 180) * this.snapAngle;
  this.angle = Math.round(d / c) * c
} else {
  this.angle = d
}
},
cancel: function () {
this.callback('cancel', null);
this.finalize()
},
finalize: function () {
this.origin = null;
this.radius = this.options.radius
},
clear: function () {
if (this.layer) {
  this.layer.renderer.clear();
  this.layer.destroyFeatures()
}
},
callback: function (b, a) {
if (this.callbacks[b]) {
  this.callbacks[b].apply(this.control, [
    this.feature.geometry.clone()
  ])
}
if (!this.persist && (b == 'done' || b == 'cancel')) {
  this.clear()
}
},
CLASS_NAME: 'OpenLayers.Handler.RegularPolygon'
});
OpenLayers.Icon = OpenLayers.Class({
url: null,
size: null,
offset: null,
calculateOffset: null,
imageDiv: null,
px: null,
initialize: function (a, b, d, c) {
this.url = a;
this.size = b || {
  w: 20,
  h: 20
};
this.offset = d || {
  x: - (this.size.w / 2),
  y: - (this.size.h / 2)
};
this.calculateOffset = c;
var e = OpenLayers.Util.createUniqueID('OL_Icon_');
this.imageDiv = OpenLayers.Util.createAlphaImageDiv(e)
},
destroy: function () {
this.erase();
OpenLayers.Event.stopObservingElement(this.imageDiv.firstChild);
this.imageDiv.innerHTML = '';
this.imageDiv = null
},
clone: function () {
return new OpenLayers.Icon(this.url, this.size, this.offset, this.calculateOffset)
},
setSize: function (a) {
if (a != null) {
  this.size = a
}
this.draw()
},
setUrl: function (a) {
if (a != null) {
  this.url = a
}
this.draw()
},
draw: function (a) {
OpenLayers.Util.modifyAlphaImageDiv(this.imageDiv, null, null, this.size, this.url, 'absolute');
this.moveTo(a);
return this.imageDiv
},
erase: function () {
if (this.imageDiv != null && this.imageDiv.parentNode != null) {
  OpenLayers.Element.remove(this.imageDiv)
}
},
setOpacity: function (a) {
OpenLayers.Util.modifyAlphaImageDiv(this.imageDiv, null, null, null, null, null, null, null, a)
},
moveTo: function (a) {
if (a != null) {
  this.px = a
}
if (this.imageDiv != null) {
  if (this.px == null) {
    this.display(false)
  } else {
    if (this.calculateOffset) {
      this.offset = this.calculateOffset(this.size)
    }
    OpenLayers.Util.modifyAlphaImageDiv(this.imageDiv, null, {
      x: this.px.x + this.offset.x,
      y: this.px.y + this.offset.y
    })
  }
}
},
display: function (a) {
this.imageDiv.style.display = (a) ? '' : 'none'
},
isDrawn: function () {
var a = (this.imageDiv && this.imageDiv.parentNode && (this.imageDiv.parentNode.nodeType != 11));
return a
},
CLASS_NAME: 'OpenLayers.Icon'
});
OpenLayers.Marker = OpenLayers.Class({
icon: null,
lonlat: null,
events: null,
map: null,
initialize: function (c, b) {
this.lonlat = c;
var a = (b) ? b : OpenLayers.Marker.defaultIcon();
if (this.icon == null) {
  this.icon = a
} else {
  this.icon.url = a.url;
  this.icon.size = a.size;
  this.icon.offset = a.offset;
  this.icon.calculateOffset = a.calculateOffset
}
this.events = new OpenLayers.Events(this, this.icon.imageDiv)
},
destroy: function () {
this.erase();
this.map = null;
this.events.destroy();
this.events = null;
if (this.icon != null) {
  this.icon.destroy();
  this.icon = null
}
},
draw: function (a) {
return this.icon.draw(a)
},
erase: function () {
if (this.icon != null) {
  this.icon.erase()
}
},
moveTo: function (a) {
if ((a != null) && (this.icon != null)) {
  this.icon.moveTo(a)
}
this.lonlat = this.map.getLonLatFromLayerPx(a)
},
isDrawn: function () {
var a = (this.icon && this.icon.isDrawn());
return a
},
onScreen: function () {
var b = false;
if (this.map) {
  var a = this.map.getExtent();
  b = a.containsLonLat(this.lonlat)
}
return b
},
inflate: function (a) {
if (this.icon) {
  this.icon.setSize({
    w: this.icon.size.w * a,
    h: this.icon.size.h * a
  })
}
},
setOpacity: function (a) {
this.icon.setOpacity(a)
},
setUrl: function (a) {
this.icon.setUrl(a)
},
display: function (a) {
this.icon.display(a)
},
CLASS_NAME: 'OpenLayers.Marker'
});
OpenLayers.Marker.defaultIcon = function () {
return new OpenLayers.Icon(OpenLayers.Util.getImageLocation('marker.png'), {
w: 21,
h: 25
}, {
x: - 10.5,
y: - 25
})
};
OpenLayers.Handler.Feature = OpenLayers.Class(OpenLayers.Handler, {
EVENTMAP: {
click: {
  'in': 'click',
  out: 'clickout'
},
mousemove: {
  'in': 'over',
  out: 'out'
},
dblclick: {
  'in': 'dblclick',
  out: null
},
mousedown: {
  'in': null,
  out: null
},
mouseup: {
  'in': null,
  out: null
},
touchstart: {
  'in': 'click',
  out: 'clickout'
}
},
feature: null,
lastFeature: null,
down: null,
up: null,
clickTolerance: 4,
geometryTypes: null,
stopClick: true,
stopDown: true,
stopUp: false,
initialize: function (d, b, c, a) {
OpenLayers.Handler.prototype.initialize.apply(this, [
  d,
  c,
  a
]);
this.layer = b
},
touchstart: function (a) {
this.startTouch();
return OpenLayers.Event.isMultiTouch(a) ? true : this.mousedown(a)
},
touchmove: function (a) {
OpenLayers.Event.preventDefault(a)
},
mousedown: function (a) {
if (OpenLayers.Event.isLeftClick(a) || OpenLayers.Event.isSingleTouch(a)) {
  this.down = a.xy
}
return this.handle(a) ? !this.stopDown : true
},
mouseup: function (a) {
this.up = a.xy;
return this.handle(a) ? !this.stopUp : true
},
click: function (a) {
return this.handle(a) ? !this.stopClick : true
},
mousemove: function (a) {
if (!this.callbacks.over && !this.callbacks.out) {
  return true
}
this.handle(a);
return true
},
dblclick: function (a) {
return !this.handle(a)
},
geometryTypeMatches: function (a) {
return this.geometryTypes == null || OpenLayers.Util.indexOf(this.geometryTypes, a.geometry.CLASS_NAME) > - 1
},
handle: function (a) {
if (this.feature && !this.feature.layer) {
  this.feature = null
}
var c = a.type;
var f = false;
var e = !!(this.feature);
var d = (c == 'click' || c == 'dblclick' || c == 'touchstart');
this.feature = this.layer.getFeatureFromEvent(a);
if (this.feature && !this.feature.layer) {
  this.feature = null
}
if (this.lastFeature && !this.lastFeature.layer) {
  this.lastFeature = null
}
if (this.feature) {
  if (c === 'touchstart') {
    OpenLayers.Event.preventDefault(a)
  }
  var b = (this.feature != this.lastFeature);
  if (this.geometryTypeMatches(this.feature)) {
    if (e && b) {
      if (this.lastFeature) {
        this.triggerCallback(c, 'out', [
          this.lastFeature
        ])
      }
      this.triggerCallback(c, 'in', [
        this.feature
      ])
    } else {
      if (!e || d) {
        this.triggerCallback(c, 'in', [
          this.feature
        ])
      }
    }
    this.lastFeature = this.feature;
    f = true
  } else {
    if (this.lastFeature && (e && b || d)) {
      this.triggerCallback(c, 'out', [
        this.lastFeature
      ])
    }
    this.feature = null
  }
} else {
  if (this.lastFeature && (e || d)) {
    this.triggerCallback(c, 'out', [
      this.lastFeature
    ])
  }
}
return f
},
triggerCallback: function (d, e, b) {
var c = this.EVENTMAP[d][e];
if (c) {
  if (d == 'click' && this.up && this.down) {
    var a = Math.sqrt(Math.pow(this.up.x - this.down.x, 2) + Math.pow(this.up.y - this.down.y, 2));
    if (a <= this.clickTolerance) {
      this.callback(c, b)
    }
    this.up = this.down = null
  } else {
    this.callback(c, b)
  }
}
},
activate: function () {
var a = false;
if (OpenLayers.Handler.prototype.activate.apply(this, arguments)) {
  this.moveLayerToTop();
  this.map.events.on({
    removelayer: this.handleMapEvents,
    changelayer: this.handleMapEvents,
    scope: this
  });
  a = true
}
return a
},
deactivate: function () {
var a = false;
if (OpenLayers.Handler.prototype.deactivate.apply(this, arguments)) {
  this.moveLayerBack();
  this.feature = null;
  this.lastFeature = null;
  this.down = null;
  this.up = null;
  this.map.events.un({
    removelayer: this.handleMapEvents,
    changelayer: this.handleMapEvents,
    scope: this
  });
  a = true
}
return a
},
handleMapEvents: function (a) {
if (a.type == 'removelayer' || a.property == 'order') {
  this.moveLayerToTop()
}
},
moveLayerToTop: function () {
var a = Math.max(this.map.Z_INDEX_BASE.Feature - 1, this.layer.getZIndex()) + 1;
this.layer.setZIndex(a)
},
moveLayerBack: function () {
var a = this.layer.getZIndex() - 1;
if (a >= this.map.Z_INDEX_BASE.Feature) {
  this.layer.setZIndex(a)
} else {
  this.map.setLayerZIndex(this.layer, this.map.getLayerIndex(this.layer))
}
},
CLASS_NAME: 'OpenLayers.Handler.Feature'
});
OpenLayers.Control.SelectFeature = OpenLayers.Class(OpenLayers.Control, {
multipleKey: null,
toggleKey: null,
multiple: false,
clickout: true,
toggle: false,
hover: false,
highlightOnly: false,
box: false,
onBeforeSelect: function () {
},
onSelect: function () {
},
onUnselect: function () {
},
scope: null,
geometryTypes: null,
layer: null,
layers: null,
callbacks: null,
selectStyle: null,
renderIntent: 'select',
handlers: null,
initialize: function (c, a) {
OpenLayers.Control.prototype.initialize.apply(this, [
  a
]);
if (this.scope === null) {
  this.scope = this
}
this.initLayer(c);
var b = {
  click: this.clickFeature,
  clickout: this.clickoutFeature
};
if (this.hover) {
  b.over = this.overFeature;
  b.out = this.outFeature
}
this.callbacks = OpenLayers.Util.extend(b, this.callbacks);
this.handlers = {
  feature: new OpenLayers.Handler.Feature(this, this.layer, this.callbacks, {
    geometryTypes: this.geometryTypes
  })
};
if (this.box) {
  this.handlers.box = new OpenLayers.Handler.Box(this, {
    done: this.selectBox
  }, {
    boxDivClassName: 'olHandlerBoxSelectFeature'
  })
}
},
initLayer: function (a) {
if (OpenLayers.Util.isArray(a)) {
  this.layers = a;
  this.layer = new OpenLayers.Layer.Vector.RootContainer(this.id + '_container', {
    layers: a
  })
} else {
  this.layer = a
}
},
destroy: function () {
if (this.active && this.layers) {
  this.map.removeLayer(this.layer)
}
OpenLayers.Control.prototype.destroy.apply(this, arguments);
if (this.layers) {
  this.layer.destroy()
}
},
activate: function () {
if (!this.active) {
  if (this.layers) {
    this.map.addLayer(this.layer)
  }
  this.handlers.feature.activate();
  if (this.box && this.handlers.box) {
    this.handlers.box.activate()
  }
}
return OpenLayers.Control.prototype.activate.apply(this, arguments)
},
deactivate: function () {
if (this.active) {
  this.handlers.feature.deactivate();
  if (this.handlers.box) {
    this.handlers.box.deactivate()
  }
  if (this.layers) {
    this.map.removeLayer(this.layer)
  }
}
return OpenLayers.Control.prototype.deactivate.apply(this, arguments)
},
unselectAll: function (b) {
var f = this.layers || [this.layer],
d,
c,
a,
e;
for (a = 0; a < f.length; ++a) {
  d = f[a];
  e = 0;
  if (d.selectedFeatures != null) {
    while (d.selectedFeatures.length > e) {
      c = d.selectedFeatures[e];
      if (!b || b.except != c) {
        this.unselect(c)
      } else {
        ++e
      }
    }
  }
}
},
clickFeature: function (a) {
if (!this.hover) {
  var b = (OpenLayers.Util.indexOf(a.layer.selectedFeatures, a) > - 1);
  if (b) {
    if (this.toggleSelect()) {
      this.unselect(a)
    } else {
      if (!this.multipleSelect()) {
        this.unselectAll({
          except: a
        })
      }
    }
  } else {
    if (!this.multipleSelect()) {
      this.unselectAll({
        except: a
      })
    }
    this.select(a)
  }
}
},
multipleSelect: function () {
return this.multiple || (this.handlers.feature.evt && this.handlers.feature.evt[this.multipleKey])
},
toggleSelect: function () {
return this.toggle || (this.handlers.feature.evt && this.handlers.feature.evt[this.toggleKey])
},
clickoutFeature: function (a) {
if (!this.hover && this.clickout) {
  this.unselectAll()
}
},
overFeature: function (b) {
var a = b.layer;
if (this.hover) {
  if (this.highlightOnly) {
    this.highlight(b)
  } else {
    if (OpenLayers.Util.indexOf(a.selectedFeatures, b) == - 1) {
      this.select(b)
    }
  }
}
},
outFeature: function (a) {
if (this.hover) {
  if (this.highlightOnly) {
    if (a._lastHighlighter == this.id) {
      if (a._prevHighlighter && a._prevHighlighter != this.id) {
        delete a._lastHighlighter;
        var b = this.map.getControl(a._prevHighlighter);
        if (b) {
          b.highlight(a)
        }
      } else {
        this.unhighlight(a)
      }
    }
  } else {
    this.unselect(a)
  }
}
},
highlight: function (c) {
var b = c.layer;
var a = this.events.triggerEvent('beforefeaturehighlighted', {
  feature: c
});
if (a !== false) {
  c._prevHighlighter = c._lastHighlighter;
  c._lastHighlighter = this.id;
  var d = this.selectStyle || this.renderIntent;
  b.drawFeature(c, d);
  this.events.triggerEvent('featurehighlighted', {
    feature: c
  })
}
},
unhighlight: function (b) {
var a = b.layer;
if (b._prevHighlighter == undefined) {
  delete b._lastHighlighter
} else {
  if (b._prevHighlighter == this.id) {
    delete b._prevHighlighter
  } else {
    b._lastHighlighter = b._prevHighlighter;
    delete b._prevHighlighter
  }
}
a.drawFeature(b, b.style || b.layer.style || 'default');
this.events.triggerEvent('featureunhighlighted', {
  feature: b
})
},
select: function (c) {
var a = this.onBeforeSelect.call(this.scope, c);
var b = c.layer;
if (a !== false) {
  a = b.events.triggerEvent('beforefeatureselected', {
    feature: c
  });
  if (a !== false) {
    b.selectedFeatures.push(c);
    this.highlight(c);
    if (!this.handlers.feature.lastFeature) {
      this.handlers.feature.lastFeature = b.selectedFeatures[0]
    }
    b.events.triggerEvent('featureselected', {
      feature: c
    });
    this.onSelect.call(this.scope, c)
  }
}
},
unselect: function (b) {
var a = b.layer;
this.unhighlight(b);
OpenLayers.Util.removeItem(a.selectedFeatures, b);
a.events.triggerEvent('featureunselected', {
  feature: b
});
this.onUnselect.call(this.scope, b)
},
selectBox: function (e) {
if (e instanceof OpenLayers.Bounds) {
  var h = this.map.getLonLatFromPixel({
    x: e.left,
    y: e.bottom
  });
  var k = this.map.getLonLatFromPixel({
    x: e.right,
    y: e.top
  });
  var a = new OpenLayers.Bounds(h.lon, h.lat, k.lon, k.lat);
  if (!this.multipleSelect()) {
    this.unselectAll()
  }
  var j = this.multiple;
  this.multiple = true;
  var d = this.layers || [this.layer];
  this.events.triggerEvent('boxselectionstart', {
    layers: d
  });
  var f;
  for (var b = 0; b < d.length;
  ++b) {
    f = d[b];
    for (var c = 0, g = f.features.length; c < g; ++c) {
      var m = f.features[c];
      if (!m.getVisibility()) {
        continue
      }
      if (this.geometryTypes == null || OpenLayers.Util.indexOf(this.geometryTypes, m.geometry.CLASS_NAME) > - 1) {
        if (a.toGeometry().intersects(m.geometry)) {
          if (OpenLayers.Util.indexOf(f.selectedFeatures, m) == - 1) {
            this.select(m)
          }
        }
      }
    }
  }
  this.multiple = j;
  this.events.triggerEvent('boxselectionend', {
    layers: d
  })
}
},
setMap: function (a) {
this.handlers.feature.setMap(a);
if (this.box) {
  this.handlers.box.setMap(a)
}
OpenLayers.Control.prototype.setMap.apply(this, arguments)
},
setLayer: function (b) {
var a = this.active;
this.unselectAll();
this.deactivate();
if (this.layers) {
  this.layer.destroy();
  this.layers = null
}
this.initLayer(b);
this.handlers.feature.layer = this.layer;
if (a) {
  this.activate()
}
},
CLASS_NAME: 'OpenLayers.Control.SelectFeature'
});
OpenLayers.Popup.Anchored = OpenLayers.Class(OpenLayers.Popup, {
relativePosition: null,
keepInMap: true,
anchor: null,
initialize: function (h, d, g, c, b, f, e) {
var a = [
  h,
  d,
  g,
  c,
  f,
  e
];
OpenLayers.Popup.prototype.initialize.apply(this, a);
this.anchor = (b != null) ? b : {
  size: new OpenLayers.Size(0, 0),
  offset: new OpenLayers.Pixel(0, 0)
}
},
destroy: function () {
this.anchor = null;
this.relativePosition = null;
OpenLayers.Popup.prototype.destroy.apply(this, arguments)
},
show: function () {
this.updatePosition();
OpenLayers.Popup.prototype.show.apply(this, arguments)
},
moveTo: function (b) {
var a = this.relativePosition;
this.relativePosition = this.calculateRelativePosition(b);
OpenLayers.Popup.prototype.moveTo.call(this, this.calculateNewPx(b));
if (this.relativePosition != a) {
  this.updateRelativePosition()
}
},
setSize: function (b) {
OpenLayers.Popup.prototype.setSize.apply(this, arguments);
if ((this.lonlat) && (this.map)) {
  var a = this.map.getLayerPxFromLonLat(this.lonlat);
  this.moveTo(a)
}
},
calculateRelativePosition: function (b) {
var d = this.map.getLonLatFromLayerPx(b);
var c = this.map.getExtent();
var a = c.determineQuadrant(d);
return OpenLayers.Bounds.oppositeQuadrant(a)
},
updateRelativePosition: function () {
},
calculateNewPx: function (b) {
var e = b.offset(this.anchor.offset);
var a = this.size || this.contentSize;
var d = (this.relativePosition.charAt(0) == 't');
e.y += (d) ? - a.h : this.anchor.size.h;
var c = (this.relativePosition.charAt(1) == 'l');
e.x += (c) ? - a.w : this.anchor.size.w;
return e
},
CLASS_NAME: 'OpenLayers.Popup.Anchored'
});
OpenLayers.Popup.Framed = OpenLayers.Class(OpenLayers.Popup.Anchored, {
imageSrc: null,
imageSize: null,
isAlphaImage: false,
positionBlocks: null,
blocks: null,
fixedRelativePosition: false,
initialize: function (g, c, f, b, a, e, d) {
OpenLayers.Popup.Anchored.prototype.initialize.apply(this, arguments);
if (this.fixedRelativePosition) {
  this.updateRelativePosition();
  this.calculateRelativePosition = function (h) {
    return this.relativePosition
  }
}
this.contentDiv.style.position = 'absolute';
this.contentDiv.style.zIndex = 1;
if (e) {
  this.closeDiv.style.zIndex = 1
}
this.groupDiv.style.position = 'absolute';
this.groupDiv.style.top = '0px';
this.groupDiv.style.left = '0px';
this.groupDiv.style.height = '100%';
this.groupDiv.style.width = '100%'
},
destroy: function () {
this.imageSrc = null;
this.imageSize = null;
this.isAlphaImage = null;
this.fixedRelativePosition = false;
this.positionBlocks = null;
for (var a = 0; a < this.blocks.length; a++) {
  var b = this.blocks[a];
  if (b.image) {
    b.div.removeChild(b.image)
  }
  b.image = null;
  if (b.div) {
    this.groupDiv.removeChild(b.div)
  }
  b.div = null
}
this.blocks = null;
OpenLayers.Popup.Anchored.prototype.destroy.apply(this, arguments)
},
setBackgroundColor: function (a) {
},
setBorder: function () {
},
setOpacity: function (a) {
},
setSize: function (a) {
OpenLayers.Popup.Anchored.prototype.setSize.apply(this, arguments);
this.updateBlocks()
},
updateRelativePosition: function () {
this.padding = this.positionBlocks[this.relativePosition].padding;
if (this.closeDiv) {
  var a = this.getContentDivPadding();
  this.closeDiv.style.right = a.right + this.padding.right + 'px';
  this.closeDiv.style.top = a.top + this.padding.top + 'px'
}
this.updateBlocks()
},
calculateNewPx: function (a) {
var b = OpenLayers.Popup.Anchored.prototype.calculateNewPx.apply(this, arguments);
b = b.offset(this.positionBlocks[this.relativePosition].offset);
return b
},
createBlocks: function () {
this.blocks = [
];
var f = null;
for (var e in this.positionBlocks) {
  f = e;
  break
}
var a = this.positionBlocks[f];
for (var d = 0; d < a.blocks.length; d++) {
  var h = {
  };
  this.blocks.push(h);
  var b = this.id + '_FrameDecorationDiv_' + d;
  h.div = OpenLayers.Util.createDiv(b, null, null, null, 'absolute', null, 'hidden', null);
  var c = this.id + '_FrameDecorationImg_' + d;
  var g = (this.isAlphaImage) ? OpenLayers.Util.createAlphaImageDiv : OpenLayers.Util.createImage;
  h.image = g(c, null, this.imageSize, this.imageSrc, 'absolute', null, null, null);
  h.div.appendChild(h.image);
  this.groupDiv.appendChild(h.div)
}
},
updateBlocks: function () {
if (!this.blocks) {
  this.createBlocks()
}
if (this.size && this.relativePosition) {
  var j = this.positionBlocks[this.relativePosition];
  for (var f = 0; f < j.blocks.length; f++) {
    var c = j.blocks[f];
    var e = this.blocks[f];
    var d = c.anchor.left;
    var k = c.anchor.bottom;
    var a = c.anchor.right;
    var n = c.anchor.top;
    var m = (isNaN(c.size.w)) ? this.size.w - (a + d)  : c.size.w;
    var g = (isNaN(c.size.h)) ? this.size.h - (k + n)  : c.size.h;
    e.div.style.width = (m < 0 ? 0 : m) + 'px';
    e.div.style.height = (g < 0 ? 0 : g) + 'px';
    e.div.style.left = (d != null) ? d + 'px' : '';
    e.div.style.bottom = (k != null) ? k + 'px' : '';
    e.div.style.right = (a != null) ? a + 'px' : '';
    e.div.style.top = (n != null) ? n + 'px' : '';
    e.image.style.left = c.position.x + 'px';
    e.image.style.top = c.position.y + 'px'
  }
  this.contentDiv.style.left = this.padding.left + 'px';
  this.contentDiv.style.top = this.padding.top + 'px'
}
},
CLASS_NAME: 'OpenLayers.Popup.Framed'
});
OpenLayers.Popup.FramedCloud = OpenLayers.Class(OpenLayers.Popup.Framed, {
contentDisplayClass: 'olFramedCloudPopupContent',
autoSize: true,
panMapIfOutOfView: true,
imageSize: new OpenLayers.Size(1276, 736),
isAlphaImage: false,
fixedRelativePosition: false,
positionBlocks: {
tl: {
  offset: new OpenLayers.Pixel(44, 0),
  padding: new OpenLayers.Bounds(8, 40, 8, 9),
  blocks: [
    {
      size: new OpenLayers.Size('auto', 'auto'),
      anchor: new OpenLayers.Bounds(0, 51, 22, 0),
      position: new OpenLayers.Pixel(0, 0)
    },
    {
      size: new OpenLayers.Size(22, 'auto'),
      anchor: new OpenLayers.Bounds(null, 50, 0, 0),
      position: new OpenLayers.Pixel( - 1238, 0)
    },
    {
      size: new OpenLayers.Size('auto', 19),
      anchor: new OpenLayers.Bounds(0, 32, 22, null),
      position: new OpenLayers.Pixel(0, - 631)
    },
    {
      size: new OpenLayers.Size(22, 18),
      anchor: new OpenLayers.Bounds(null, 32, 0, null),
      position: new OpenLayers.Pixel( - 1238, - 632)
    },
    {
      size: new OpenLayers.Size(81, 35),
      anchor: new OpenLayers.Bounds(null, 0, 0, null),
      position: new OpenLayers.Pixel(0, - 688)
    }
  ]
},
tr: {
  offset: new OpenLayers.Pixel( - 45, 0),
  padding: new OpenLayers.Bounds(8, 40, 8, 9),
  blocks: [
    {
      size: new OpenLayers.Size('auto', 'auto'),
      anchor: new OpenLayers.Bounds(0, 51, 22, 0),
      position: new OpenLayers.Pixel(0, 0)
    },
    {
      size: new OpenLayers.Size(22, 'auto'),
      anchor: new OpenLayers.Bounds(null, 50, 0, 0),
      position: new OpenLayers.Pixel( - 1238, 0)
    },
    {
      size: new OpenLayers.Size('auto', 19),
      anchor: new OpenLayers.Bounds(0, 32, 22, null),
      position: new OpenLayers.Pixel(0, - 631)
    },
    {
      size: new OpenLayers.Size(22, 19),
      anchor: new OpenLayers.Bounds(null, 32, 0, null),
      position: new OpenLayers.Pixel( - 1238, - 631)
    },
    {
      size: new OpenLayers.Size(81, 35),
      anchor: new OpenLayers.Bounds(0, 0, null, null),
      position: new OpenLayers.Pixel( - 215, - 687)
    }
  ]
},
bl: {
  offset: new OpenLayers.Pixel(45, 0),
  padding: new OpenLayers.Bounds(8, 9, 8, 40),
  blocks: [
    {
      size: new OpenLayers.Size('auto', 'auto'),
      anchor: new OpenLayers.Bounds(0, 21, 22, 32),
      position: new OpenLayers.Pixel(0, 0)
    },
    {
      size: new OpenLayers.Size(22, 'auto'),
      anchor: new OpenLayers.Bounds(null, 21, 0, 32),
      position: new OpenLayers.Pixel( - 1238, 0)
    },
    {
      size: new OpenLayers.Size('auto', 21),
      anchor: new OpenLayers.Bounds(0, 0, 22, null),
      position: new OpenLayers.Pixel(0, - 629)
    },
    {
      size: new OpenLayers.Size(22, 21),
      anchor: new OpenLayers.Bounds(null, 0, 0, null),
      position: new OpenLayers.Pixel( - 1238, - 629)
    },
    {
      size: new OpenLayers.Size(81, 33),
      anchor: new OpenLayers.Bounds(null, null, 0, 0),
      position: new OpenLayers.Pixel( - 101, - 674)
    }
  ]
},
br: {
  offset: new OpenLayers.Pixel( - 44, 0),
  padding: new OpenLayers.Bounds(8, 9, 8, 40),
  blocks: [
    {
      size: new OpenLayers.Size('auto', 'auto'),
      anchor: new OpenLayers.Bounds(0, 21, 22, 32),
      position: new OpenLayers.Pixel(0, 0)
    },
    {
      size: new OpenLayers.Size(22, 'auto'),
      anchor: new OpenLayers.Bounds(null, 21, 0, 32),
      position: new OpenLayers.Pixel( - 1238, 0)
    },
    {
      size: new OpenLayers.Size('auto', 21),
      anchor: new OpenLayers.Bounds(0, 0, 22, null),
      position: new OpenLayers.Pixel(0, - 629)
    },
    {
      size: new OpenLayers.Size(22, 21),
      anchor: new OpenLayers.Bounds(null, 0, 0, null),
      position: new OpenLayers.Pixel( - 1238, - 629)
    },
    {
      size: new OpenLayers.Size(81, 33),
      anchor: new OpenLayers.Bounds(0, null, null, 0),
      position: new OpenLayers.Pixel( - 311, - 674)
    }
  ]
}
},
minSize: new OpenLayers.Size(105, 10),
maxSize: new OpenLayers.Size(1200, 660),
initialize: function (g, c, f, b, a, e, d) {
this.imageSrc = '/js/carto/img/cloud-popup-relative.png';
OpenLayers.Popup.Framed.prototype.initialize.apply(this, arguments);
this.contentDiv.className = this.contentDisplayClass
},
CLASS_NAME: 'OpenLayers.Popup.FramedCloud'
});
OpenLayers.Filter = OpenLayers.Class({
initialize: function (a) {
OpenLayers.Util.extend(this, a)
},
destroy: function () {
},
evaluate: function (a) {
return true
},
clone: function () {
return null
},
toString: function () {
var a;
if (OpenLayers.Format && OpenLayers.Format.CQL) {
  a = OpenLayers.Format.CQL.prototype.write(this)
} else {
  a = Object.prototype.toString.call(this)
}
return a
},
CLASS_NAME: 'OpenLayers.Filter'
});
OpenLayers.Filter.Comparison = OpenLayers.Class(OpenLayers.Filter, {
type: null,
property: null,
value: null,
matchCase: true,
lowerBoundary: null,
upperBoundary: null,
initialize: function (a) {
OpenLayers.Filter.prototype.initialize.apply(this, [
  a
]);
if (this.type === OpenLayers.Filter.Comparison.LIKE && a.matchCase === undefined) {
  this.matchCase = null
}
},
evaluate: function (c) {
if (c instanceof OpenLayers.Feature.Vector) {
  c = c.attributes
}
var a = false;
var b = c[this.property];
var e;
switch (this.type) {
  case OpenLayers.Filter.Comparison.EQUAL_TO:
    e = this.value;
    if (!this.matchCase && typeof b == 'string' && typeof e == 'string') {
      a = (b.toUpperCase() == e.toUpperCase())
    } else {
      a = (b == e)
    }
    break;
  case OpenLayers.Filter.Comparison.NOT_EQUAL_TO:
    e = this.value;
    if (!this.matchCase && typeof b == 'string' && typeof e == 'string') {
      a = (b.toUpperCase() != e.toUpperCase())
    } else {
      a = (b != e)
    }
    break;
  case OpenLayers.Filter.Comparison.LESS_THAN:
    a = b < this.value;
    break;
  case OpenLayers.Filter.Comparison.GREATER_THAN:
    a = b > this.value;
    break;
  case OpenLayers.Filter.Comparison.LESS_THAN_OR_EQUAL_TO:
    a = b <= this.value;
    break;
  case OpenLayers.Filter.Comparison.GREATER_THAN_OR_EQUAL_TO:
    a = b >= this.value;
    break;
  case OpenLayers.Filter.Comparison.BETWEEN:
    a = (b >= this.lowerBoundary) && (b <= this.upperBoundary);
    break;
  case OpenLayers.Filter.Comparison.LIKE:
    var d = new RegExp(this.value, 'gi');
    a = d.test(b);
    break;
  case OpenLayers.Filter.Comparison.IS_NULL:
    a = (b === null);
    break
}
return a
},
value2regex: function (c, b, a) {
if (c == '.') {
  throw new Error('\'.\' is an unsupported wildCard character for OpenLayers.Filter.Comparison')
}
c = c ? c : '*';
b = b ? b : '.';
a = a ? a : '!';
this.value = this.value.replace(new RegExp('\\' + a + '(.|$)', 'g'), '\\$1');
this.value = this.value.replace(new RegExp('\\' + b, 'g'), '.');
this.value = this.value.replace(new RegExp('\\' + c, 'g'), '.*');
this.value = this.value.replace(new RegExp('\\\\.\\*', 'g'), '\\' + c);
this.value = this.value.replace(new RegExp('\\\\\\.', 'g'), '\\' + b);
return this.value
},
regex2value: function () {
var a = this.value;
a = a.replace(/!/g, '!!');
a = a.replace(/(\\)?\\\./g, function (c, b) {
  return b ? c : '!.'
});
a = a.replace(/(\\)?\\\*/g, function (c, b) {
  return b ? c : '!*'
});
a = a.replace(/\\\\/g, '\\');
a = a.replace(/\.\*/g, '*');
return a
},
clone: function () {
return OpenLayers.Util.extend(new OpenLayers.Filter.Comparison(), this)
},
CLASS_NAME: 'OpenLayers.Filter.Comparison'
});
OpenLayers.Filter.Comparison.EQUAL_TO = '=='; OpenLayers.Filter.Comparison.NOT_EQUAL_TO = '!='; OpenLayers.Filter.Comparison.LESS_THAN = '<'; OpenLayers.Filter.Comparison.GREATER_THAN = '>'; OpenLayers.Filter.Comparison.LESS_THAN_OR_EQUAL_TO = '<='; OpenLayers.Filter.Comparison.GREATER_THAN_OR_EQUAL_TO = '>='; OpenLayers.Filter.Comparison.BETWEEN = '..';
OpenLayers.Filter.Comparison.LIKE = '~'; OpenLayers.Filter.Comparison.IS_NULL = 'NULL'; OpenLayers.Rule = OpenLayers.Class({
id: null,
name: null,
title: null,
description: null,
context: null,
filter: null,
elseFilter: false,
symbolizer: null,
symbolizers: null,
minScaleDenominator: null,
maxScaleDenominator: null,
initialize: function (a) {
this.symbolizer = {
};
OpenLayers.Util.extend(this, a);
if (this.symbolizers) {
  delete this.symbolizer
}
this.id = OpenLayers.Util.createUniqueID(this.CLASS_NAME + '_')
},
destroy: function () {
for (var a in this.symbolizer) {
  this.symbolizer[a] = null
}
this.symbolizer = null;
delete this.symbolizers
},
evaluate: function (c) {
var b = this.getContext(c);
var a = true;
if (this.minScaleDenominator || this.maxScaleDenominator) {
  var d = c.layer.map.getScale()
}
if (this.minScaleDenominator) {
  a = d >= OpenLayers.Style.createLiteral(this.minScaleDenominator, b)
}
if (a && this.maxScaleDenominator) {
  a = d < OpenLayers.Style.createLiteral(this.maxScaleDenominator, b)
}
if (a && this.filter) {
  if (this.filter.CLASS_NAME == 'OpenLayers.Filter.FeatureId') {
    a = this.filter.evaluate(c)
  } else {
    a = this.filter.evaluate(b)
  }
}
return a
},
getContext: function (b) {
var a = this.context;
if (!a) {
  a = b.attributes || b.data
}
if (typeof this.context == 'function') {
  a = this.context(b)
}
return a
},
clone: function () {
var b = OpenLayers.Util.extend({
}, this);
if (this.symbolizers) {
  var a = this.symbolizers.length;
  b.symbolizers = new Array(a);
  for (var d = 0; d < a; ++d) {
    b.symbolizers[d] = this.symbolizers[d].clone()
  }
} else {
  b.symbolizer = {
  };
  var f,
  e;
  for (var c in this.symbolizer) {
    f = this.symbolizer[c];
    e = typeof f;
    if (e === 'object') {
      b.symbolizer[c] = OpenLayers.Util.extend({
      }, f)
    } else {
      if (e === 'string') {
        b.symbolizer[c] = f
      }
    }
  }
}
b.filter = this.filter && this.filter.clone();
b.context = this.context && OpenLayers.Util.extend({
}, this.context);
return new OpenLayers.Rule(b)
},
CLASS_NAME: 'OpenLayers.Rule'
}); OpenLayers.Handler.Keyboard = OpenLayers.Class(OpenLayers.Handler, {
KEY_EVENTS: [
'keydown',
'keyup'
],
eventListener: null,
observeElement: null,
initialize: function (c, b, a) {
OpenLayers.Handler.prototype.initialize.apply(this, arguments);
this.eventListener = OpenLayers.Function.bindAsEventListener(this.handleKeyEvent, this)
},
destroy: function () {
this.deactivate();
this.eventListener = null;
OpenLayers.Handler.prototype.destroy.apply(this, arguments)
},
activate: function () {
if (OpenLayers.Handler.prototype.activate.apply(this, arguments)) {
  this.observeElement = this.observeElement || document;
  for (var b = 0, a = this.KEY_EVENTS.length; b < a; b++) {
    OpenLayers.Event.observe(this.observeElement, this.KEY_EVENTS[b], this.eventListener)
  }
  return true
} else {
  return false
}
},
deactivate: function () {
var c = false;
if (OpenLayers.Handler.prototype.deactivate.apply(this, arguments)) {
  for (var b = 0, a = this.KEY_EVENTS.length; b < a;
  b++) {
    OpenLayers.Event.stopObserving(this.observeElement, this.KEY_EVENTS[b], this.eventListener)
  }
  c = true
}
return c
},
handleKeyEvent: function (a) {
if (this.checkModifiers(a)) {
  this.callback(a.type, [
    a
  ])
}
},
CLASS_NAME: 'OpenLayers.Handler.Keyboard'
}); OpenLayers.Control.DragFeature = OpenLayers.Class(OpenLayers.Control, {
geometryTypes: null,
onStart: function (b, a) {
},
onDrag: function (b, a) {
},
onComplete: function (b, a) {
},
onEnter: function (a) {
},
onLeave: function (a) {
},
documentDrag: false,
layer: null,
feature: null,
dragCallbacks: {
},
featureCallbacks: {
},
lastPixel: null,
initialize: function (b, a) {
OpenLayers.Control.prototype.initialize.apply(this, [
  a
]);
this.layer = b;
this.handlers = {
  drag: new OpenLayers.Handler.Drag(this, OpenLayers.Util.extend({
    down: this.downFeature,
    move: this.moveFeature,
    up: this.upFeature,
    out: this.cancel,
    done: this.doneDragging
  }, this.dragCallbacks), {
    documentDrag: this.documentDrag
  }),
  feature: new OpenLayers.Handler.Feature(this, this.layer, OpenLayers.Util.extend({
    click: this.clickFeature,
    clickout: this.clickoutFeature,
    over: this.overFeature,
    out: this.outFeature
  }, this.featureCallbacks), {
    geometryTypes: this.geometryTypes
  })
}
},
clickFeature: function (a) {
if (this.handlers.feature.touch && !this.over && this.overFeature(a)) {
  this.handlers.drag.dragstart(this.handlers.feature.evt);
  this.handlers.drag.stopDown = false
}
},
clickoutFeature: function (a) {
if (this.handlers.feature.touch && this.over) {
  this.outFeature(a);
  this.handlers.drag.stopDown = true
}
},
destroy: function () {
this.layer = null;
OpenLayers.Control.prototype.destroy.apply(this, [
])
},
activate: function () {
return (this.handlers.feature.activate() && OpenLayers.Control.prototype.activate.apply(this, arguments))
},
deactivate: function () {
this.handlers.drag.deactivate();
this.handlers.feature.deactivate();
this.feature = null;
this.dragging = false;
this.lastPixel = null;
OpenLayers.Element.removeClass(this.map.viewPortDiv, this.displayClass + 'Over');
return OpenLayers.Control.prototype.deactivate.apply(this, arguments)
},
overFeature: function (b) {
var a = false;
if (!this.handlers.drag.dragging) {
  this.feature = b;
  this.handlers.drag.activate();
  a = true;
  this.over = true;
  OpenLayers.Element.addClass(this.map.viewPortDiv, this.displayClass + 'Over');
  this.onEnter(b)
} else {
  if (this.feature.id == b.id) {
    this.over = true
  } else {
    this.over = false
  }
}
return a
},
downFeature: function (a) {
this.lastPixel = a;
this.onStart(this.feature, a)
},
moveFeature: function (a) {
var b = this.map.getResolution();
this.feature.geometry.move(b * (a.x - this.lastPixel.x), b * (this.lastPixel.y - a.y));
this.layer.drawFeature(this.feature);
this.lastPixel = a;
this.onDrag(this.feature, a)
},
upFeature: function (a) {
if (!this.over) {
  this.handlers.drag.deactivate()
}
},
doneDragging: function (a) {
this.onComplete(this.feature, a)
},
outFeature: function (a) {
if (!this.handlers.drag.dragging) {
  this.over = false;
  this.handlers.drag.deactivate();
  OpenLayers.Element.removeClass(this.map.viewPortDiv, this.displayClass + 'Over');
  this.onLeave(a);
  this.feature = null
} else {
  if (this.feature.id == a.id) {
    this.over = false
  }
}
},
cancel: function () {
this.handlers.drag.deactivate();
this.over = false
},
setMap: function (a) {
this.handlers.drag.setMap(a);
this.handlers.feature.setMap(a);
OpenLayers.Control.prototype.setMap.apply(this, arguments)
},
CLASS_NAME: 'OpenLayers.Control.DragFeature'
}); OpenLayers.Control.ModifyFeature = OpenLayers.Class(OpenLayers.Control, {
documentDrag: false,
geometryTypes: null,
clickout: true,
toggle: true,
standalone: false,
layer: null,
feature: null,
vertex: null,
vertices: null,
virtualVertices: null,
handlers: null,
deleteCodes: null,
virtualStyle: null,
vertexRenderIntent: null,
mode: null,
createVertices: true,
modified: false,
radiusHandle: null,
dragHandle: null,
onModificationStart: function () {
},
onModification: function () {
},
onModificationEnd: function () {
},
initialize: function (c, b) {
b = b || {
};
this.layer = c;
this.vertices = [
];
this.virtualVertices = [
];
this.virtualStyle = OpenLayers.Util.extend({
}, this.layer.style || this.layer.styleMap.createSymbolizer(null, b.vertexRenderIntent));
this.virtualStyle.fillOpacity = 0.3;
this.virtualStyle.strokeOpacity = 0.3;
this.deleteCodes = [
  46,
  68
];
this.mode = OpenLayers.Control.ModifyFeature.RESHAPE;
OpenLayers.Control.prototype.initialize.apply(this, [
  b
]);
if (!(OpenLayers.Util.isArray(this.deleteCodes))) {
  this.deleteCodes = [
    this.deleteCodes
  ]
}
var d = {
  down: function (f) {
    this.vertex = null;
    var g = this.layer.getFeatureFromEvent(this.handlers.drag.evt);
    if (g) {
      this.dragStart(g)
    } else {
      if (this.clickout) {
        this._unselect = this.feature
      }
    }
  },
  move: function (f) {
    delete this._unselect;
    if (this.vertex) {
      this.dragVertex(this.vertex, f)
    }
  },
  up: function () {
    this.handlers.drag.stopDown = false;
    if (this._unselect) {
      this.unselectFeature(this._unselect);
      delete this._unselect
    }
  },
  done: function (f) {
    if (this.vertex) {
      this.dragComplete(this.vertex)
    }
  }
};
var a = {
  documentDrag: this.documentDrag,
  stopDown: false
};
var e = {
  keydown: this.handleKeypress
};
this.handlers = {
  keyboard: new OpenLayers.Handler.Keyboard(this, e),
  drag: new OpenLayers.Handler.Drag(this, d, a)
}
},
destroy: function () {
if (this.map) {
  this.map.events.un({
    removelayer: this.handleMapEvents,
    changelayer: this.handleMapEvents,
    scope: this
  })
}
this.layer = null;
OpenLayers.Control.prototype.destroy.apply(this, [
])
},
activate: function () {
this.moveLayerToTop();
this.map.events.on({
  removelayer: this.handleMapEvents,
  changelayer: this.handleMapEvents,
  scope: this
});
return (this.handlers.keyboard.activate() && this.handlers.drag.activate() && OpenLayers.Control.prototype.activate.apply(this, arguments))
},
deactivate: function () {
var b = false;
if (OpenLayers.Control.prototype.deactivate.apply(this, arguments)) {
  this.moveLayerBack();
  this.map.events.un({
    removelayer: this.handleMapEvents,
    changelayer: this.handleMapEvents,
    scope: this
  });
  this.layer.removeFeatures(this.vertices, {
    silent: true
  });
  this.layer.removeFeatures(this.virtualVertices, {
    silent: true
  });
  this.vertices = [
  ];
  this.handlers.drag.deactivate();
  this.handlers.keyboard.deactivate();
  var a = this.feature;
  if (a && a.geometry && a.layer) {
    this.unselectFeature(a)
  }
  b = true
}
return b
},
beforeSelectFeature: function (a) {
return this.layer.events.triggerEvent('beforefeaturemodified', {
  feature: a
})
},
selectFeature: function (b) {
if (this.feature === b || (this.geometryTypes && OpenLayers.Util.indexOf(this.geometryTypes, b.geometry.CLASS_NAME) == - 1)) {
  return
}
if (this.beforeSelectFeature(b) !== false) {
  if (this.feature) {
    this.unselectFeature(this.feature)
  }
  this.feature = b;
  this.layer.selectedFeatures.push(b);
  this.layer.drawFeature(b, 'select');
  this.modified = false;
  this.resetVertices();
  this.onModificationStart(this.feature)
}
var a = b.modified;
if (b.geometry && !(a && a.geometry)) {
  this._originalGeometry = b.geometry.clone()
}
},
unselectFeature: function (a) {
this.layer.removeFeatures(this.vertices, {
  silent: true
});
this.vertices = [
];
this.layer.destroyFeatures(this.virtualVertices, {
  silent: true
});
this.virtualVertices = [
];
if (this.dragHandle) {
  this.layer.destroyFeatures([this.dragHandle], {
    silent: true
  });
  delete this.dragHandle
}
if (this.radiusHandle) {
  this.layer.destroyFeatures([this.radiusHandle], {
    silent: true
  });
  delete this.radiusHandle
}
this.layer.drawFeature(this.feature, 'default');
this.feature = null;
OpenLayers.Util.removeItem(this.layer.selectedFeatures, a);
this.onModificationEnd(a);
this.layer.events.triggerEvent('afterfeaturemodified', {
  feature: a,
  modified: this.modified
});
this.modified = false
},
dragStart: function (b) {
var a = b.geometry.CLASS_NAME == 'OpenLayers.Geometry.Point';
if (!this.standalone && ((!b._sketch && a) || !b._sketch)) {
  if (this.toggle && this.feature === b) {
    this._unselect = b
  }
  this.selectFeature(b)
}
if (b._sketch || a) {
  this.vertex = b;
  this.handlers.drag.stopDown = true
}
},
dragVertex: function (c, a) {
var d = this.map.getLonLatFromViewPortPx(a);
var b = c.geometry;
b.move(d.lon - b.x, d.lat - b.y);
this.modified = true;
if (this.feature.geometry.CLASS_NAME == 'OpenLayers.Geometry.Point') {
  this.layer.events.triggerEvent('vertexmodified', {
    vertex: c.geometry,
    feature: this.feature,
    pixel: a
  })
} else {
  if (c._index) {
    c.geometry.parent.addComponent(c.geometry, c._index);
    delete c._index;
    OpenLayers.Util.removeItem(this.virtualVertices, c);
    this.vertices.push(c)
  } else {
    if (c == this.dragHandle) {
      this.layer.removeFeatures(this.vertices, {
        silent: true
      });
      this.vertices = [
      ];
      if (this.radiusHandle) {
        this.layer.destroyFeatures([this.radiusHandle], {
          silent: true
        });
        this.radiusHandle = null
      }
    } else {
      if (c !== this.radiusHandle) {
        this.layer.events.triggerEvent('vertexmodified', {
          vertex: c.geometry,
          feature: this.feature,
          pixel: a
        })
      }
    }
  }
  if (this.virtualVertices.length > 0) {
    this.layer.destroyFeatures(this.virtualVertices, {
      silent: true
    });
    this.virtualVertices = [
    ]
  }
  this.layer.drawFeature(this.feature, this.standalone ? undefined : 'select')
}
this.layer.drawFeature(c)
},
dragComplete: function (a) {
this.resetVertices();
this.setFeatureState();
this.onModification(this.feature);
this.layer.events.triggerEvent('featuremodified', {
  feature: this.feature
})
},
setFeatureState: function () {
if (this.feature.state != OpenLayers.State.INSERT && this.feature.state != OpenLayers.State.DELETE) {
  this.feature.state = OpenLayers.State.UPDATE;
  if (this.modified && this._originalGeometry) {
    var a = this.feature;
    a.modified = OpenLayers.Util.extend(a.modified, {
      geometry: this._originalGeometry
    });
    delete this._originalGeometry
  }
}
},
resetVertices: function () {
if (this.vertices.length > 0) {
  this.layer.removeFeatures(this.vertices, {
    silent: true
  });
  this.vertices = [
  ]
}
if (this.virtualVertices.length > 0) {
  this.layer.removeFeatures(this.virtualVertices, {
    silent: true
  });
  this.virtualVertices = [
  ]
}
if (this.dragHandle) {
  this.layer.destroyFeatures([this.dragHandle], {
    silent: true
  });
  this.dragHandle = null
}
if (this.radiusHandle) {
  this.layer.destroyFeatures([this.radiusHandle], {
    silent: true
  });
  this.radiusHandle = null
}
if (this.feature && this.feature.geometry.CLASS_NAME != 'OpenLayers.Geometry.Point') {
  if ((this.mode & OpenLayers.Control.ModifyFeature.DRAG)) {
    this.collectDragHandle()
  }
  if ((this.mode & (OpenLayers.Control.ModifyFeature.ROTATE | OpenLayers.Control.ModifyFeature.RESIZE))) {
    this.collectRadiusHandle()
  }
  if (this.mode & OpenLayers.Control.ModifyFeature.RESHAPE) {
    if (!(this.mode & OpenLayers.Control.ModifyFeature.RESIZE)) {
      this.collectVertices()
    }
  }
}
},
handleKeypress: function (a) {
var b = a.keyCode;
if (this.feature && OpenLayers.Util.indexOf(this.deleteCodes, b) != - 1) {
  var c = this.layer.getFeatureFromEvent(this.handlers.drag.evt);
  if (c && OpenLayers.Util.indexOf(this.vertices, c) != - 1 && !this.handlers.drag.dragging && c.geometry.parent) {
    c.geometry.parent.removeComponent(c.geometry);
    this.layer.events.triggerEvent('vertexremoved', {
      vertex: c.geometry,
      feature: this.feature,
      pixel: a.xy
    });
    this.layer.drawFeature(this.feature, this.standalone ? undefined : 'select');
    this.modified = true;
    this.resetVertices();
    this.setFeatureState();
    this.onModification(this.feature);
    this.layer.events.triggerEvent('featuremodified', {
      feature: this.feature
    })
  }
}
},
collectVertices: function () {
this.vertices = [
];
this.virtualVertices = [
];
var a = this;
function b(h) {
  var d,
  e,
  j,
  f;
  if (h.CLASS_NAME == 'OpenLayers.Geometry.Point') {
    e = new OpenLayers.Feature.Vector(h);
    e._sketch = true;
    e.renderIntent = a.vertexRenderIntent;
    a.vertices.push(e)
  } else {
    var c = h.components.length;
    if (h.CLASS_NAME == 'OpenLayers.Geometry.LinearRing') {
      c -= 1
    }
    for (d = 0; d < c; ++d) {
      j = h.components[d];
      if (j.CLASS_NAME == 'OpenLayers.Geometry.Point') {
        e = new OpenLayers.Feature.Vector(j);
        e._sketch = true;
        e.renderIntent = a.vertexRenderIntent;
        a.vertices.push(e)
      } else {
        b(j)
      }
    }
    if (a.createVertices && h.CLASS_NAME != 'OpenLayers.Geometry.MultiPoint') {
      for (d = 0, f = h.components.length; d < f - 1; ++d) {
        var m = h.components[d];
        var n = h.components[d + 1];
        if (m.CLASS_NAME == 'OpenLayers.Geometry.Point' && n.CLASS_NAME == 'OpenLayers.Geometry.Point') {
          var k = (m.x + n.x) / 2;
          var g = (m.y + n.y) / 2;
          var l = new OpenLayers.Feature.Vector(new OpenLayers.Geometry.Point(k, g), null, a.virtualStyle);
          l.geometry.parent = h;
          l._index = d + 1;
          l._sketch = true;
          a.virtualVertices.push(l)
        }
      }
    }
  }
}
b.call(this, this.feature.geometry);
this.layer.addFeatures(this.virtualVertices, {
  silent: true
});
this.layer.addFeatures(this.vertices, {
  silent: true
})
},
collectDragHandle: function () {
var d = this.feature.geometry;
var a = d.getBounds().getCenterLonLat();
var c = new OpenLayers.Geometry.Point(a.lon, a.lat);
var b = new OpenLayers.Feature.Vector(c);
c.move = function (e, f) {
  OpenLayers.Geometry.Point.prototype.move.call(this, e, f);
  d.move(e, f)
};
b._sketch = true;
this.dragHandle = b;
this.dragHandle.renderIntent = this.vertexRenderIntent;
this.layer.addFeatures([this.dragHandle], {
  silent: true
})
},
collectRadiusHandle: function () {
var h = this.feature.geometry;
var a = h.getBounds();
var b = a.getCenterLonLat();
var i = new OpenLayers.Geometry.Point(b.lon, b.lat);
var g = new OpenLayers.Geometry.Point(a.right, a.bottom);
var f = new OpenLayers.Feature.Vector(g);
var c = (this.mode & OpenLayers.Control.ModifyFeature.RESIZE);
var e = (this.mode & OpenLayers.Control.ModifyFeature.RESHAPE);
var d = (this.mode & OpenLayers.Control.ModifyFeature.ROTATE);
g.move = function (t, s) {
  OpenLayers.Geometry.Point.prototype.move.call(this, t, s);
  var u = this.x - i.x;
  var p = this.y - i.y;
  var v = u - t;
  var q = p - s;
  if (d) {
    var k = Math.atan2(q, v);
    var j = Math.atan2(p, u);
    var n = j - k;
    n *= 180 / Math.PI;
    h.rotate(n, i)
  }
  if (c) {
    var m,
    r;
    if (e) {
      m = p / q;
      r = (u / v) / m
    } else {
      var o = Math.sqrt((v * v) + (q * q));
      var l = Math.sqrt((u * u) + (p * p));
      m = l / o
    }
    h.resize(m, i, r)
  }
};
f._sketch = true;
this.radiusHandle = f;
this.radiusHandle.renderIntent = this.vertexRenderIntent;
this.layer.addFeatures([this.radiusHandle], {
  silent: true
})
},
setMap: function (a) {
this.handlers.drag.setMap(a);
OpenLayers.Control.prototype.setMap.apply(this, arguments)
},
handleMapEvents: function (a) {
if (a.type == 'removelayer' || a.property == 'order') {
  this.moveLayerToTop()
}
},
moveLayerToTop: function () {
var a = Math.max(this.map.Z_INDEX_BASE.Feature - 1, this.layer.getZIndex()) + 1;
this.layer.setZIndex(a)
},
moveLayerBack: function () {
var a = this.layer.getZIndex() - 1;
if (a >= this.map.Z_INDEX_BASE.Feature) {
  this.layer.setZIndex(a)
} else {
  this.map.setLayerZIndex(this.layer, this.map.getLayerIndex(this.layer))
}
},
CLASS_NAME: 'OpenLayers.Control.ModifyFeature'
}); OpenLayers.Control.ModifyFeature.RESHAPE = 1;
OpenLayers.Control.ModifyFeature.RESIZE = 2; OpenLayers.Control.ModifyFeature.ROTATE = 4; OpenLayers.Control.ModifyFeature.DRAG = 8; OpenLayers.Handler.Pinch = OpenLayers.Class(OpenLayers.Handler, {
started: false,
stopDown: false,
pinching: false,
last: null,
start: null,
touchstart: function (b) {
var a = true;
this.pinching = false;
if (OpenLayers.Event.isMultiTouch(b)) {
  this.started = true;
  this.last = this.start = {
    distance: this.getDistance(b.touches),
    delta: 0,
    scale: 1
  };
  this.callback('start', [
    b,
    this.start
  ]);
  a = !this.stopDown
} else {
  if (this.started) {
    return false
  } else {
    this.started = false;
    this.start = null;
    this.last = null
  }
}
OpenLayers.Event.preventDefault(b);
return a
},
touchmove: function (a) {
if (this.started && OpenLayers.Event.isMultiTouch(a)) {
  this.pinching = true;
  var b = this.getPinchData(a);
  this.callback('move', [
    a,
    b
  ]);
  this.last = b;
  OpenLayers.Event.stop(a)
} else {
  if (this.started) {
    return false
  }
}
return true
},
touchend: function (a) {
if (this.started && !OpenLayers.Event.isMultiTouch(a)) {
  this.started = false;
  this.pinching = false;
  this.callback('done', [
    a,
    this.start,
    this.last
  ]);
  this.start = null;
  this.last = null;
  return false
}
return true
},
activate: function () {
var a = false;
if (OpenLayers.Handler.prototype.activate.apply(this, arguments)) {
  this.pinching = false;
  a = true
}
return a
},
deactivate: function () {
var a = false;
if (OpenLayers.Handler.prototype.deactivate.apply(this, arguments)) {
  this.started = false;
  this.pinching = false;
  this.start = null;
  this.last = null;
  a = true
}
return a
},
getDistance: function (c) {
var b = c[0];
var a = c[1];
return Math.sqrt(Math.pow(b.olClientX - a.olClientX, 2) + Math.pow(b.olClientY - a.olClientY, 2))
},
getPinchData: function (a) {
var c = this.getDistance(a.touches);
var b = c / this.start.distance;
return {
  distance: c,
  delta: this.last.distance - c,
  scale: b
}
},
CLASS_NAME: 'OpenLayers.Handler.Pinch'
}); OpenLayers.Control.PinchZoom = OpenLayers.Class(OpenLayers.Control, {
type: OpenLayers.Control.TYPE_TOOL,
pinchOrigin: null,
currentCenter: null,
autoActivate: true,
preserveCenter: false,
initialize: function (a) {
OpenLayers.Control.prototype.initialize.apply(this, arguments);
this.handler = new OpenLayers.Handler.Pinch(this, {
  start: this.pinchStart,
  move: this.pinchMove,
  done: this.pinchDone
}, this.handlerOptions)
},
pinchStart: function (a, c) {
var b = (this.preserveCenter) ? this.map.getPixelFromLonLat(this.map.getCenter())  : a.xy;
this.pinchOrigin = b;
this.currentCenter = b
},
pinchMove: function (b, h) {
var g = h.scale;
var f = this.map.layerContainerOriginPx;
var d = this.pinchOrigin;
var e = (this.preserveCenter) ? this.map.getPixelFromLonLat(this.map.getCenter())  : b.xy;
var c = Math.round((f.x + e.x - d.x) + (g - 1) * (f.x - d.x));
var a = Math.round((f.y + e.y - d.y) + (g - 1) * (f.y - d.y));
this.map.applyTransform(c, a, g);
this.currentCenter = e
},
pinchDone: function (b, h, g) {
this.map.applyTransform();
var f = this.map.getZoomForResolution(this.map.getResolution() / g.scale, true);
if (f !== this.map.getZoom() || !this.currentCenter.equals(this.pinchOrigin)) {
  var d = this.map.getResolutionForZoom(f);
  var a = this.map.getLonLatFromPixel(this.pinchOrigin);
  var c = this.currentCenter;
  var e = this.map.getSize();
  a.lon += d * ((e.w / 2) - c.x);
  a.lat -= d * ((e.h / 2) - c.y);
  this.map.div.clientWidth = this.map.div.clientWidth;
  this.map.setCenter(a, f)
}
},
CLASS_NAME: 'OpenLayers.Control.PinchZoom'
}); OpenLayers.Handler.Hover = OpenLayers.Class(OpenLayers.Handler, {
delay: 500,
pixelTolerance: null,
stopMove: false,
px: null,
timerId: null,
mousemove: function (a) {
if (this.passesTolerance(a.xy)) {
  this.clearTimer();
  this.callback('move', [
    a
  ]);
  this.px = a.xy;
  a = OpenLayers.Util.extend({
  }, a);
  this.timerId = window.setTimeout(OpenLayers.Function.bind(this.delayedCall, this, a), this.delay)
}
return !this.stopMove
},
mouseout: function (a) {
if (OpenLayers.Util.mouseLeft(a, this.map.viewPortDiv)) {
  this.clearTimer();
  this.callback('move', [
    a
  ])
}
return true
},
passesTolerance: function (b) {
var c = true;
if (this.pixelTolerance && this.px) {
  var a = Math.sqrt(Math.pow(this.px.x - b.x, 2) + Math.pow(this.px.y - b.y, 2));
  if (a < this.pixelTolerance) {
    c = false
  }
}
return c
},
clearTimer: function () {
if (this.timerId != null) {
  window.clearTimeout(this.timerId);
  this.timerId = null
}
},
delayedCall: function (a) {
this.callback('pause', [
  a
])
},
deactivate: function () {
var a = false;
if (OpenLayers.Handler.prototype.deactivate.apply(this, arguments)) {
  this.clearTimer();
  a = true
}
return a
},
CLASS_NAME: 'OpenLayers.Handler.Hover'
}); OpenLayers.Layer.WMTS = OpenLayers.Class(OpenLayers.Layer.Grid, {
isBaseLayer: true,
version: '1.0.0',
requestEncoding: 'KVP',
url: null,
layer: null,
matrixSet: null,
style: null,
format: 'image/jpeg',
tileOrigin: null,
tileFullExtent: null,
formatSuffix: null,
matrixIds: null,
dimensions: null,
params: null,
zoomOffset: 0,
serverResolutions: null,
formatSuffixMap: {
'image/png': 'png',
'image/png8': 'png',
'image/png24': 'png',
'image/png32': 'png',
png: 'png',
'image/jpeg': 'jpg',
'image/jpg': 'jpg',
jpeg: 'jpg',
jpg: 'jpg'
},
matrix: null,
initialize: function (c) {
var f = {
  url: true,
  layer: true,
  style: true,
  matrixSet: true
};
for (var g in f) {
  if (!(g in c)) {
    throw new Error('Missing property \'' + g + '\' in layer configuration.')
  }
}
c.params = OpenLayers.Util.upperCaseObject(c.params);
var b = [
  c.name,
  c.url,
  c.params,
  c
];
OpenLayers.Layer.Grid.prototype.initialize.apply(this, b);
if (!this.formatSuffix) {
  this.formatSuffix = this.formatSuffixMap[this.format] || this.format.split('/').pop()
}
if (this.matrixIds) {
  var a = this.matrixIds.length;
  if (a && typeof this.matrixIds[0] === 'string') {
    var e = this.matrixIds;
    this.matrixIds = new Array(a);
    for (var d = 0; d < a; ++d) {
      this.matrixIds[d] = {
        identifier: e[d]
      }
    }
  }
}
},
setMap: function () {
OpenLayers.Layer.Grid.prototype.setMap.apply(this, arguments)
},
updateMatrixProperties: function () {
this.matrix = this.getMatrix();
if (this.matrix) {
  if (this.matrix.topLeftCorner) {
    this.tileOrigin = this.matrix.topLeftCorner
  }
  if (this.matrix.tileWidth && this.matrix.tileHeight) {
    this.tileSize = new OpenLayers.Size(this.matrix.tileWidth, this.matrix.tileHeight)
  }
  if (!this.tileOrigin) {
    this.tileOrigin = new OpenLayers.LonLat(this.maxExtent.left, this.maxExtent.top)
  }
  if (!this.tileFullExtent) {
    this.tileFullExtent = this.maxExtent
  }
}
},
moveTo: function (b, a, c) {
if (a || !this.matrix) {
  this.updateMatrixProperties()
}
return OpenLayers.Layer.Grid.prototype.moveTo.apply(this, arguments)
},
clone: function (a) {
if (a == null) {
  a = new OpenLayers.Layer.WMTS(this.options)
}
a = OpenLayers.Layer.Grid.prototype.clone.apply(this, [
  a
]);
return a
},
getIdentifier: function () {
return this.getServerZoom()
},
getMatrix: function () {
var b;
if (!this.matrixIds || this.matrixIds.length === 0) {
  b = {
    identifier: this.getIdentifier()
  }
} else {
  if ('scaleDenominator' in this.matrixIds[0]) {
    var a = OpenLayers.METERS_PER_INCH * OpenLayers.INCHES_PER_UNIT[this.units] * this.getServerResolution() / 0.00028;
    var e = Number.POSITIVE_INFINITY;
    var f;
    for (var c = 0, d = this.matrixIds.length; c < d; ++c) {
      f = Math.abs(1 - (this.matrixIds[c].scaleDenominator / a));
      if (f < e) {
        e = f;
        b = this.matrixIds[c]
      }
    }
  } else {
    b = this.matrixIds[this.getIdentifier()]
  }
}
return b
},
getTileInfo: function (f) {
var b = this.getServerResolution();
var d = (f.lon - this.tileOrigin.lon) / (b * this.tileSize.w);
var c = (this.tileOrigin.lat - f.lat) / (b * this.tileSize.h);
var a = Math.floor(d);
var e = Math.floor(c);
return {
  col: a,
  row: e,
  i: Math.floor((d - a) * this.tileSize.w),
  j: Math.floor((c - e) * this.tileSize.h)
}
},
getURL: function (a) {
a = this.adjustBounds(a);
var d = '';
if (!this.tileFullExtent || this.tileFullExtent.intersectsBounds(a)) {
  var c = a.getCenterLonLat();
  var f = this.getTileInfo(c);
  var k = this.matrix.identifier;
  var b = this.dimensions,
  g;
  if (OpenLayers.Util.isArray(this.url)) {
    d = this.selectUrl([this.version,
    this.style,
    this.matrixSet,
    this.matrix.identifier,
    f.row,
    f.col].join(','), this.url)
  } else {
    d = this.url
  }
  if (this.requestEncoding.toUpperCase() === 'REST') {
    g = this.params;
    if (d.indexOf('{') !== - 1) {
      var l = d.replace(/\{/g, '${');
      var e = {
        style: this.style,
        Style: this.style,
        TileMatrixSet: this.matrixSet,
        TileMatrix: this.matrix.identifier,
        TileRow: f.row,
        TileCol: f.col
      };
      if (b) {
        var h,
        j;
        for (j = b.length - 1; j >= 0; --j) {
          h = b[j];
          e[h] = g[h.toUpperCase()]
        }
      }
      d = OpenLayers.String.format(l, e)
    } else {
      var m = this.version + '/' + this.layer + '/' + this.style + '/';
      if (b) {
        for (var j = 0; j < b.length; j++) {
          if (g[b[j]]) {
            m = m + g[b[j]] + '/'
          }
        }
      }
      m = m + this.matrixSet + '/' + this.matrix.identifier + '/' + f.row + '/' + f.col + '.' + this.formatSuffix;
      if (!d.match(/\/$/)) {
        d = d + '/'
      }
      d = d + m
    }
  } else {
    if (this.requestEncoding.toUpperCase() === 'KVP') {
      g = {
        SERVICE: 'WMTS',
        REQUEST: 'GetTile',
        VERSION: this.version,
        LAYER: this.layer,
        STYLE: this.style,
        TILEMATRIXSET: this.matrixSet,
        TILEMATRIX: this.matrix.identifier,
        TILEROW: f.row,
        TILECOL: f.col,
        FORMAT: this.format
      };
      d = OpenLayers.Layer.Grid.prototype.getFullRequestString.apply(this, [
        g
      ])
    }
  }
}
return d
},
mergeNewParams: function (a) {
if (this.requestEncoding.toUpperCase() === 'KVP') {
  return OpenLayers.Layer.Grid.prototype.mergeNewParams.apply(this, [
    OpenLayers.Util.upperCaseObject(a)
  ])
}
},
CLASS_NAME: 'OpenLayers.Layer.WMTS'
}); OpenLayers.Strategy = OpenLayers.Class({
layer: null,
options: null,
active: null,
autoActivate: true,
autoDestroy: true,
initialize: function (a) {
OpenLayers.Util.extend(this, a);
this.options = a;
this.active = false
},
destroy: function () {
this.deactivate();
this.layer = null;
this.options = null
},
setLayer: function (a) {
this.layer = a
},
activate: function () {
if (!this.active) {
  this.active = true;
  return true
}
return false
},
deactivate: function () {
if (this.active) {
  this.active = false;
  return true
}
return false
},
CLASS_NAME: 'OpenLayers.Strategy'
}); OpenLayers.Strategy.BBOX = OpenLayers.Class(OpenLayers.Strategy, {
bounds: null,
resolution: null,
ratio: 2,
resFactor: null,
response: null,
activate: function () {
var a = OpenLayers.Strategy.prototype.activate.call(this);
if (a) {
  this.layer.events.on({
    moveend: this.update,
    refresh: this.update,
    visibilitychanged: this.update,
    scope: this
  });
  this.update()
}
return a
},
deactivate: function () {
var a = OpenLayers.Strategy.prototype.deactivate.call(this);
if (a) {
  this.layer.events.un({
    moveend: this.update,
    refresh: this.update,
    visibilitychanged: this.update,
    scope: this
  })
}
return a
},
update: function (b) {
var a = this.getMapBounds();
if (a !== null && ((b && b.force) || (this.layer.visibility && this.layer.calculateInRange() && this.invalidBounds(a)))) {
  this.calculateBounds(a);
  this.resolution = this.layer.map.getResolution();
  this.triggerRead(b)
}
},
getMapBounds: function () {
if (this.layer.map === null) {
  return null
}
var a = this.layer.map.getExtent();
if (a && !this.layer.projection.equals(this.layer.map.getProjectionObject())) {
  a = a.clone().transform(this.layer.map.getProjectionObject(), this.layer.projection)
}
return a
},
invalidBounds: function (a) {
if (!a) {
  a = this.getMapBounds()
}
var c = !this.bounds || !this.bounds.containsBounds(a);
if (!c && this.resFactor) {
  var b = this.resolution / this.layer.map.getResolution();
  c = (b >= this.resFactor || b <= (1 / this.resFactor))
}
return c
},
calculateBounds: function (b) {
if (!b) {
  b = this.getMapBounds()
}
var a = b.getCenterLonLat();
var d = b.getWidth() * this.ratio;
var c = b.getHeight() * this.ratio;
this.bounds = new OpenLayers.Bounds(a.lon - (d / 2), a.lat - (c / 2), a.lon + (d / 2), a.lat + (c / 2))
},
triggerRead: function (b) {
if (this.response && !(b && b.noAbort === true)) {
  this.layer.protocol.abort(this.response);
  this.layer.events.triggerEvent('loadend')
}
var a = {
  filter: this.createFilter()
};
this.layer.events.triggerEvent('loadstart', a);
this.response = this.layer.protocol.read(OpenLayers.Util.applyDefaults({
  filter: a.filter,
  callback: this.merge,
  scope: this
}, b))
},
createFilter: function () {
var a = new OpenLayers.Filter.Spatial({
  type: OpenLayers.Filter.Spatial.BBOX,
  value: this.bounds,
  projection: this.layer.projection
});
if (this.layer.filter) {
  a = new OpenLayers.Filter.Logical({
    type: OpenLayers.Filter.Logical.AND,
    filters: [
      this.layer.filter,
      a
    ]
  })
}
return a
},
merge: function (g) {
this.layer.destroyFeatures();
if (g.success()) {
  var e = g.features;
  if (e && e.length > 0) {
    var f = this.layer.projection;
    var d = this.layer.map.getProjectionObject();
    if (!d.equals(f)) {
      var c;
      for (var b = 0, a = e.length; b < a; ++b) {
        c = e[b].geometry;
        if (c) {
          c.transform(f, d)
        }
      }
    }
    this.layer.addFeatures(e)
  }
} else {
  this.bounds = null
}
this.response = null;
this.layer.events.triggerEvent('loadend', {
  response: g
})
},
CLASS_NAME: 'OpenLayers.Strategy.BBOX'
}); OpenLayers.Filter.Spatial = OpenLayers.Class(OpenLayers.Filter, {
type: null,
property: null,
value: null,
distance: null,
distanceUnits: null,
evaluate: function (c) {
var a = false;
switch (this.type) {
  case OpenLayers.Filter.Spatial.BBOX:
  case OpenLayers.Filter.Spatial.INTERSECTS:
    if (c.geometry) {
      var b = this.value;
      if (this.value.CLASS_NAME == 'OpenLayers.Bounds') {
        b = this.value.toGeometry()
      }
      if (c.geometry.intersects(b)) {
        a = true
      }
    }
    break;
  default:
    throw new Error('evaluate is not implemented for this filter type.')
}
return a
},
clone: function () {
var a = OpenLayers.Util.applyDefaults({
  value: this.value && this.value.clone && this.value.clone()
}, this);
return new OpenLayers.Filter.Spatial(a)
},
CLASS_NAME: 'OpenLayers.Filter.Spatial'
}); OpenLayers.Filter.Spatial.BBOX = 'BBOX'; OpenLayers.Filter.Spatial.INTERSECTS = 'INTERSECTS'; OpenLayers.Filter.Spatial.DWITHIN = 'DWITHIN';
OpenLayers.Filter.Spatial.WITHIN = 'WITHIN'; OpenLayers.Filter.Spatial.CONTAINS = 'CONTAINS'; OpenLayers.Filter.Logical = OpenLayers.Class(OpenLayers.Filter, {
filters: null,
type: null,
initialize: function (a) {
this.filters = [
];
OpenLayers.Filter.prototype.initialize.apply(this, [
  a
])
},
destroy: function () {
this.filters = null;
OpenLayers.Filter.prototype.destroy.apply(this)
},
evaluate: function (c) {
var b,
a;
switch (this.type) {
  case OpenLayers.Filter.Logical.AND:
    for (b = 0, a = this.filters.length; b < a; b++) {
      if (this.filters[b].evaluate(c) == false) {
        return false
      }
    }
    return true;
  case OpenLayers.Filter.Logical.OR:
    for (b = 0, a = this.filters.length; b < a;
    b++) {
      if (this.filters[b].evaluate(c) == true) {
        return true
      }
    }
    return false;
  case OpenLayers.Filter.Logical.NOT:
    return (!this.filters[0].evaluate(c))
}
return undefined
},
clone: function () {
var c = [
];
for (var b = 0, a = this.filters.length; b < a; ++b) {
  c.push(this.filters[b].clone())
}
return new OpenLayers.Filter.Logical({
  type: this.type,
  filters: c
})
},
CLASS_NAME: 'OpenLayers.Filter.Logical'
}); OpenLayers.Filter.Logical.AND = '&&'; OpenLayers.Filter.Logical.OR = '||'; OpenLayers.Filter.Logical.NOT = '!'; OpenLayers.Format.QueryStringFilter = (function () {
var b = {
};
b[OpenLayers.Filter.Comparison.EQUAL_TO] = 'eq';
b[OpenLayers.Filter.Comparison.NOT_EQUAL_TO] = 'ne';
b[OpenLayers.Filter.Comparison.LESS_THAN] = 'lt';
b[OpenLayers.Filter.Comparison.LESS_THAN_OR_EQUAL_TO] = 'lte';
b[OpenLayers.Filter.Comparison.GREATER_THAN] = 'gt';
b[OpenLayers.Filter.Comparison.GREATER_THAN_OR_EQUAL_TO] = 'gte';
b[OpenLayers.Filter.Comparison.LIKE] = 'ilike';
function a(c) {
c = c.replace(/%/g, '\\%');
c = c.replace(/\\\\\.(\*)?/g, function (e, d) {
  return d ? e : '\\\\_'
});
c = c.replace(/\\\\\.\*/g, '\\\\%');
c = c.replace(/(\\)?\.(\*)?/g, function (e, d, f) {
  return d || f ? e : '_'
});
c = c.replace(/(\\)?\.\*/g, function (e, d) {
  return d ? e : '%'
});
c = c.replace(/\\\./g, '.');
c = c.replace(/(\\)?\\\*/g, function (e, d) {
  return d ? e : '*'
});
return c
}
return OpenLayers.Class(OpenLayers.Format, {
wildcarded: false,
srsInBBOX: false,
write: function (f, j) {
  j = j || {
  };
  var e = f.CLASS_NAME;
  var g = e.substring(e.lastIndexOf('.') + 1);
  switch (g) {
    case 'Spatial':
      switch (f.type) {
        case OpenLayers.Filter.Spatial.BBOX:
          j.bbox = f.value.toArray();
          if (this.srsInBBOX && f.projection) {
            j.bbox.push(f.projection.getCode())
          }
          break;
        case OpenLayers.Filter.Spatial.DWITHIN:
          j.tolerance = f.distance;
        case OpenLayers.Filter.Spatial.WITHIN:
          j.lon = f.value.x;
          j.lat = f.value.y;
          break;
        default:
          OpenLayers.Console.warn('Unknown spatial filter type ' + f.type)
      }
      break;
    case 'Comparison':
      var k = b[f.type];
      if (k !== undefined) {
        var h = f.value;
        if (f.type == OpenLayers.Filter.Comparison.LIKE) {
          h = a(h);
          if (this.wildcarded) {
            h = '%' + h + '%'
          }
        }
        j[f.property + '__' + k] = h;
        j.queryable = j.queryable || [];
        j.queryable.push(f.property)
    } else {
      OpenLayers.Console.warn('Unknown comparison filter type ' + f.type)
  }
  break;
case 'Logical':
  if (f.type === OpenLayers.Filter.Logical.AND) {
    for (var d = 0, c = f.filters.length; d < c; d++) {
      j = this.write(f.filters[d], j)
    }
} else {
  OpenLayers.Console.warn('Unsupported logical filter type ' + f.type)
}
break;
default:
OpenLayers.Console.warn('Unknown filter type ' + g)
}
return j
},
CLASS_NAME: 'OpenLayers.Format.QueryStringFilter'
})
}) ();
OpenLayers.TileManager = OpenLayers.Class({
cacheSize: 256,
tilesPerFrame: 2,
frameDelay: 16,
moveDelay: 100,
zoomDelay: 200,
maps: null,
tileQueueId: null,
tileQueue: null,
tileCache: null,
tileCacheIndex: null,
initialize: function (a) {
OpenLayers.Util.extend(this, a);
this.maps = [
];
this.tileQueueId = {
};
this.tileQueue = {
};
this.tileCache = {
};
this.tileCacheIndex = [
]
},
addMap: function (c) {
if (this._destroyed || !OpenLayers.Layer.Grid) {
return
}
this.maps.push(c);
this.tileQueue[c.id] = [
];
for (var a = 0, b = c.layers.length; a < b; ++a) {
this.addLayer({
layer: c.layers[a]
})
}
c.events.on({
move: this.move,
zoomend: this.zoomEnd,
changelayer: this.changeLayer,
addlayer: this.addLayer,
preremovelayer: this.removeLayer,
scope: this
})
},
removeMap: function (c) {
if (this._destroyed || !OpenLayers.Layer.Grid) {
return
}
window.clearTimeout(this.tileQueueId[c.id]);
if (c.layers) {
for (var a = 0, b = c.layers.length; a < b; ++a) {
this.removeLayer({
layer: c.layers[a]
})
}
}
if (c.events) {
c.events.un({
move: this.move,
zoomend: this.zoomEnd,
changelayer: this.changeLayer,
addlayer: this.addLayer,
preremovelayer: this.removeLayer,
scope: this
})
}
delete this.tileQueue[c.id];
delete this.tileQueueId[c.id];
OpenLayers.Util.removeItem(this.maps, c)
},
move: function (a) {
this.updateTimeout(a.object, this.moveDelay, true)
},
zoomEnd: function (a) {
this.updateTimeout(a.object, this.zoomDelay)
},
changeLayer: function (a) {
if (a.property === 'visibility' || a.property === 'params') {
this.updateTimeout(a.object, 0)
}
},
addLayer: function (a) {
var d = a.layer;
if (d instanceof OpenLayers.Layer.Grid) {
d.events.on({
addtile: this.addTile,
retile: this.clearTileQueue,
scope: this
});
var c,
b,
e;
for (c = d.grid.length - 1; c >= 0; --c) {
for (b = d.grid[c].length - 1; b >= 0; --b) {
e = d.grid[c][b];
this.addTile({
tile: e
});
if (e.url && !e.imgDiv) {
this.manageTileCache({
  object: e
})
}
}
}
}
},
removeLayer: function (a) {
var d = a.layer;
if (d instanceof OpenLayers.Layer.Grid) {
this.clearTileQueue({
object: d
});
if (d.events) {
d.events.un({
addtile: this.addTile,
retile: this.clearTileQueue,
scope: this
})
}
if (d.grid) {
var c,
b,
e;
for (c = d.grid.length - 1; c >= 0; --c) {
for (b = d.grid[c].length - 1; b >= 0; --b) {
e = d.grid[c][b];
this.unloadTile({
  object: e
})
}
}
}
}
},
updateTimeout: function (d, a, c) {
window.clearTimeout(this.tileQueueId[d.id]);
var b = this.tileQueue[d.id];
if (!c || b.length) {
this.tileQueueId[d.id] = window.setTimeout(OpenLayers.Function.bind(function () {
this.drawTilesFromQueue(d);
if (b.length) {
this.updateTimeout(d, this.frameDelay)
}
}, this), a)
}
},
addTile: function (a) {
if (a.tile instanceof OpenLayers.Tile.Image) {
a.tile.events.on({
beforedraw: this.queueTileDraw,
beforeload: this.manageTileCache,
loadend: this.addToCache,
unload: this.unloadTile,
scope: this
})
} else {
this.removeLayer({
layer: a.tile.layer
})
}
},
unloadTile: function (a) {
var b = a.object;
b.events.un({
beforedraw: this.queueTileDraw,
beforeload: this.manageTileCache,
loadend: this.addToCache,
unload: this.unloadTile,
scope: this
});
OpenLayers.Util.removeItem(this.tileQueue[b.layer.map.id], b)
},
queueTileDraw: function (a) {
var f = a.object;
var g = false;
var d = f.layer;
var c = d.getURL(f.bounds);
var b = this.tileCache[c];
if (b && b.className !== 'olTileImage') {
delete this.tileCache[c];
OpenLayers.Util.removeItem(this.tileCacheIndex, c);
b = null
}
if (d.url && (d.async || !b)) {
var e = this.tileQueue[d.map.id];
if (!~OpenLayers.Util.indexOf(e, f)) {
e.push(f)
}
g = true
}
return !g
},
drawTilesFromQueue: function (d) {
var c = this.tileQueue[d.id];
var b = this.tilesPerFrame;
var a = d.zoomTween && d.zoomTween.playing;
while (!a && c.length && b) {
c.shift().draw(true);
--b
}
},
manageTileCache: function (a) {
var c = a.object;
var b = this.tileCache[c.url];
if (b) {
if (b.parentNode && OpenLayers.Element.hasClass(b.parentNode, 'olBackBuffer')) {
b.parentNode.removeChild(b);
b.id = null
}
if (!b.parentNode) {
b.style.visibility = 'hidden';
b.style.opacity = 0;
c.setImage(b);
OpenLayers.Util.removeItem(this.tileCacheIndex, c.url);
this.tileCacheIndex.push(c.url)
}
}
},
addToCache: function (a) {
var b = a.object;
if (!this.tileCache[b.url]) {
if (!OpenLayers.Element.hasClass(b.imgDiv, 'olImageLoadError')) {
if (this.tileCacheIndex.length >= this.cacheSize) {
delete this.tileCache[this.tileCacheIndex[0]];
this.tileCacheIndex.shift()
}
this.tileCache[b.url] = b.imgDiv;
this.tileCacheIndex.push(b.url)
}
}
},
clearTileQueue: function (a) {
var c = a.object;
var d = this.tileQueue[c.map.id];
for (var b = d.length - 1; b >= 0; --b) {
if (d[b].layer === c) {
d.splice(b, 1)
}
}
},
destroy: function () {
for (var a = this.maps.length - 1; a >= 0; --a) {
this.removeMap(this.maps[a])
}
this.maps = null;
this.tileQueue = null;
this.tileQueueId = null;
this.tileCache = null;
this.tileCacheIndex = null;
this._destroyed = true
}
});
OpenLayers.ProxyHost = '';
if (!OpenLayers.Request) {
OpenLayers.Request = {
}
}
OpenLayers.Util.extend(OpenLayers.Request, {
DEFAULT_CONFIG: {
method: 'GET',
url: window.location.href,
async: true,
user: undefined,
password: undefined,
params: null,
proxy: OpenLayers.ProxyHost,
headers: {
},
data: null,
callback: function () {
},
success: null,
failure: null,
scope: null
},
URL_SPLIT_REGEX: /([^:]*:)\/\/([^:]*:?[^@]*@)?([^:\/\?]*):?([^\/\?]*)/,
events: new OpenLayers.Events(this),
makeSameOrigin: function (b, c) {
var f = b.indexOf('http') !== 0;
var g = !f && b.match(this.URL_SPLIT_REGEX);
if (g) {
var a = window.location;
f = g[1] == a.protocol && g[3] == a.hostname;
var d = g[4],
e = a.port;
if (d != 80 && d != '' || e != '80' && e != '') {
f = f && d == e
}
}
if (!f) {
if (c) {
if (typeof c == 'function') {
b = c(b)
} else {
b = c + encodeURIComponent(b)
}
}
}
return b
},
issue: function (b) {
var d = OpenLayers.Util.extend(this.DEFAULT_CONFIG, {
proxy: OpenLayers.ProxyHost
});
b = b || {
};
b.headers = b.headers || {
};
b = OpenLayers.Util.applyDefaults(b, d);
b.headers = OpenLayers.Util.applyDefaults(b.headers, d.headers);
var g = false,
e;
for (e in b.headers) {
if (b.headers.hasOwnProperty(e)) {
if (e.toLowerCase() === 'x-requested-with') {
g = true
}
}
}
if (g === false) {
b.headers['X-Requested-With'] = 'XMLHttpRequest'
}
var c = new OpenLayers.Request.XMLHttpRequest();
var a = OpenLayers.Util.urlAppend(b.url, OpenLayers.Util.getParameterString(b.params || {
}));
a = OpenLayers.Request.makeSameOrigin(a, b.proxy);
c.open(b.method, a, b.async, b.user, b.password);
for (var f in b.headers) {
c.setRequestHeader(f, b.headers[f])
}
var i = this.events;
var h = this;
c.onreadystatechange = function () {
if (c.readyState == OpenLayers.Request.XMLHttpRequest.DONE) {
var j = i.triggerEvent('complete', {
request: c,
config: b,
requestUrl: a
});
if (j !== false) {
h.runCallbacks({
request: c,
config: b,
requestUrl: a
})
}
}
};
if (b.async === false) {
c.send(b.data)
} else {
window.setTimeout(function () {
if (c.readyState !== 0) {
c.send(b.data)
}
}, 0)
}
return c
},
runCallbacks: function (d) {
var e = d.request;
var c = d.config;
var a = (c.scope) ? OpenLayers.Function.bind(c.callback, c.scope)  : c.callback;
var f;
if (c.success) {
f = (c.scope) ? OpenLayers.Function.bind(c.success, c.scope)  : c.success
}
var b;
if (c.failure) {
b = (c.scope) ? OpenLayers.Function.bind(c.failure, c.scope)  : c.failure
}
if (OpenLayers.Util.createUrlObject(c.url).protocol == 'file:' && e.responseText) {
e.status = 200
}
a(e);
if (!e.status || (e.status >= 200 && e.status < 300)) {
this.events.triggerEvent('success', d);
if (f) {
f(e)
}
}
if (e.status && (e.status < 200 || e.status >= 300)) {
this.events.triggerEvent('failure', d);
if (b) {
b(e)
}
}
},
GET: function (a) {
a = OpenLayers.Util.extend(a, {
method: 'GET'
});
return OpenLayers.Request.issue(a)
},
POST: function (a) {
a = OpenLayers.Util.extend(a, {
method: 'POST'
});
a.headers = a.headers ? a.headers : {
};
if (!('CONTENT-TYPE' in OpenLayers.Util.upperCaseObject(a.headers))) {
a.headers['Content-Type'] = 'application/xml'
}
return OpenLayers.Request.issue(a)
},
PUT: function (a) {
a = OpenLayers.Util.extend(a, {
method: 'PUT'
});
a.headers = a.headers ? a.headers : {
};
if (!('CONTENT-TYPE' in OpenLayers.Util.upperCaseObject(a.headers))) {
a.headers['Content-Type'] = 'application/xml'
}
return OpenLayers.Request.issue(a)
},
DELETE: function (a) {
a = OpenLayers.Util.extend(a, {
method: 'DELETE'
});
return OpenLayers.Request.issue(a)
},
HEAD: function (a) {
a = OpenLayers.Util.extend(a, {
method: 'HEAD'
});
return OpenLayers.Request.issue(a)
},
OPTIONS: function (a) {
a = OpenLayers.Util.extend(a, {
method: 'OPTIONS'
});
return OpenLayers.Request.issue(a)
}
});
(function () {
var g = window.XMLHttpRequest;
var a = !!window.controllers,
j = window.document.all && !window.opera,
k = j && window.navigator.userAgent.match(/MSIE 7.0/);
function d() {
this._object = g && !k ? new g : new window.ActiveXObject('Microsoft.XMLHTTP');
this._listeners = [
]
}
function c() {
return new d
}
c.prototype = d.prototype;
if (a && g.wrapped) {
c.wrapped = g.wrapped
}
c.UNSENT = 0;
c.OPENED = 1;
c.HEADERS_RECEIVED = 2;
c.LOADING = 3;
c.DONE = 4;
c.prototype.readyState = c.UNSENT;
c.prototype.responseText = '';
c.prototype.responseXML = null;
c.prototype.status = 0;
c.prototype.statusText = '';
c.prototype.priority = 'NORMAL';
c.prototype.onreadystatechange = null;
c.onreadystatechange = null;
c.onopen = null;
c.onsend = null;
c.onabort = null;
c.prototype.open = function (o, r, n, s, m) {
delete this._headers;
if (arguments.length < 3) {
n = true
}
this._async = n;
var q = this,
p = this.readyState,
l;
if (j && n) {
l = function () {
if (p != c.DONE) {
e(q);
q.abort()
}
};
window.attachEvent('onunload', l)
}
if (c.onopen) {
c.onopen.apply(this, arguments)
}
if (arguments.length > 4) {
this._object.open(o, r, n, s, m)
} else {
if (arguments.length > 3) {
this._object.open(o, r, n, s)
} else {
this._object.open(o, r, n)
}
}
this.readyState = c.OPENED;
b(this);
this._object.onreadystatechange = function () {
if (a && !n) {
return
}
q.readyState = q._object.readyState;
h(q);
if (q._aborted) {
q.readyState = c.UNSENT;
return
}
if (q.readyState == c.DONE) {
delete q._data;
e(q);
if (j && n) {
window.detachEvent('onunload', l)
}
}
if (p != q.readyState) {
b(q)
}
p = q.readyState
}
};
function f(l) {
l._object.send(l._data);
if (a && !l._async) {
l.readyState = c.OPENED;
h(l);
while (l.readyState < c.DONE) {
l.readyState++;
b(l);
if (l._aborted) {
return
}
}
}
}
c.prototype.send = function (l) {
if (c.onsend) {
c.onsend.apply(this, arguments)
}
if (!arguments.length) {
l = null
}
if (l && l.nodeType) {
l = window.XMLSerializer ? new window.XMLSerializer().serializeToString(l)  : l.xml;
if (!this._headers['Content-Type']) {
this._object.setRequestHeader('Content-Type', 'application/xml')
}
}
this._data = l;
f(this)
};
c.prototype.abort = function () {
if (c.onabort) {
c.onabort.apply(this, arguments)
}
if (this.readyState > c.UNSENT) {
this._aborted = true
}
this._object.abort();
e(this);
this.readyState = c.UNSENT;
delete this._data
};
c.prototype.getAllResponseHeaders = function () {
return this._object.getAllResponseHeaders()
};
c.prototype.getResponseHeader = function (l) {
return this._object.getResponseHeader(l)
};
c.prototype.setRequestHeader = function (l, m) {
if (!this._headers) {
this._headers = {
}
}
this._headers[l] = m;
return this._object.setRequestHeader(l, m)
};
c.prototype.addEventListener = function (o, n, m) {
for (var l = 0, p; p = this._listeners[l];
l++) {
if (p[0] == o && p[1] == n && p[2] == m) {
return
}
}
this._listeners.push([o,
n,
m])
};
c.prototype.removeEventListener = function (o, n, m) {
for (var l = 0, p; p = this._listeners[l]; l++) {
if (p[0] == o && p[1] == n && p[2] == m) {
break
}
}
if (p) {
this._listeners.splice(l, 1)
}
};
c.prototype.dispatchEvent = function (m) {
var n = {
type: m.type,
target: this,
currentTarget: this,
eventPhase: 2,
bubbles: m.bubbles,
cancelable: m.cancelable,
timeStamp: m.timeStamp,
stopPropagation: function () {
},
preventDefault: function () {
},
initEvent: function () {
}
};
if (n.type == 'readystatechange' && this.onreadystatechange) {
(this.onreadystatechange.handleEvent || this.onreadystatechange).apply(this, [
n
])
}
for (var l = 0, o; o = this._listeners[l]; l++) {
if (o[0] == n.type && !o[2]) {
(o[1].handleEvent || o[1]).apply(this, [
n
])
}
}
};
c.prototype.toString = function () {
return '[object XMLHttpRequest]'
};
c.toString = function () {
return '[XMLHttpRequest]'
};
function b(l) {
if (c.onreadystatechange) {
c.onreadystatechange.apply(l)
}
l.dispatchEvent({
type: 'readystatechange',
bubbles: false,
cancelable: false,
timeStamp: new Date + 0
})
}
function i(n) {
var m = n.responseXML,
l = n.responseText;
if (j && l && m && !m.documentElement && n.getResponseHeader('Content-Type').match(/[^\/]+\/[^\+]+\+xml/)) {
m = new window.ActiveXObject('Microsoft.XMLDOM');
m.async = false;
m.validateOnParse = false;
m.loadXML(l)
}
if (m) {
if ((j && m.parseError != 0) || !m.documentElement || (m.documentElement && m.documentElement.tagName == 'parsererror')) {
return null
}
}
return m
}
function h(l) {
try {
l.responseText = l._object.responseText
} catch (m) {
}
try {
l.responseXML = i(l._object)
} catch (m) {
}
try {
l.status = l._object.status
} catch (m) {
}
try {
l.statusText = l._object.statusText
} catch (m) {
}
}
function e(l) {
l._object.onreadystatechange = new window.Function
}
if (!window.Function.prototype.apply) {
window.Function.prototype.apply = function (l, m) {
if (!m) {
m = [
]
}
l.__func = this;
l.__func(m[0], m[1], m[2], m[3], m[4]);
delete l.__func
}
}
if (!OpenLayers.Request) {
OpenLayers.Request = {
}
}
OpenLayers.Request.XMLHttpRequest = c
}) ();
OpenLayers.Protocol.HTTP = OpenLayers.Class(OpenLayers.Protocol, {
url: null,
headers: null,
params: null,
callback: null,
scope: null,
readWithPOST: false,
updateWithPOST: false,
deleteWithPOST: false,
wildcarded: false,
srsInBBOX: false,
initialize: function (a) {
a = a || {
};
this.params = {
};
this.headers = {
};
OpenLayers.Protocol.prototype.initialize.apply(this, arguments);
if (!this.filterToParams && OpenLayers.Format.QueryStringFilter) {
var b = new OpenLayers.Format.QueryStringFilter({
wildcarded: this.wildcarded,
srsInBBOX: this.srsInBBOX
});
this.filterToParams = function (c, d) {
return b.write(c, d)
}
}
},
destroy: function () {
this.params = null;
this.headers = null;
OpenLayers.Protocol.prototype.destroy.apply(this)
},
read: function (a) {
OpenLayers.Protocol.prototype.read.apply(this, arguments);
a = a || {
};
a.params = OpenLayers.Util.applyDefaults(a.params, this.options.params);
a = OpenLayers.Util.applyDefaults(a, this.options);
if (a.filter && this.filterToParams) {
a.params = this.filterToParams(a.filter, a.params)
}
var b = (a.readWithPOST !== undefined) ? a.readWithPOST : this.readWithPOST;
var d = new OpenLayers.Protocol.Response({
requestType: 'read'
});
if (b) {
var c = a.headers || {
};
c['Content-Type'] = 'application/x-www-form-urlencoded';
d.priv = OpenLayers.Request.POST({
url: a.url,
callback: this.createCallback(this.handleRead, d, a),
data: OpenLayers.Util.getParameterString(a.params),
headers: c
})
} else {
d.priv = OpenLayers.Request.GET({
url: a.url,
callback: this.createCallback(this.handleRead, d, a),
params: a.params,
headers: a.headers
})
}
return d
},
handleRead: function (b, a) {
this.handleResponse(b, a)
},
create: function (b, a) {
a = OpenLayers.Util.applyDefaults(a, this.options);
var c = new OpenLayers.Protocol.Response({
reqFeatures: b,
requestType: 'create'
});
c.priv = OpenLayers.Request.POST({
url: a.url,
callback: this.createCallback(this.handleCreate, c, a),
headers: a.headers,
data: this.format.write(b)
});
return c
},
handleCreate: function (b, a) {
this.handleResponse(b, a)
},
update: function (c, b) {
b = b || {
};
var a = b.url || c.url || this.options.url + '/' + c.fid;
b = OpenLayers.Util.applyDefaults(b, this.options);
var d = new OpenLayers.Protocol.Response({
reqFeatures: c,
requestType: 'update'
});
var e = this.updateWithPOST ? 'POST' : 'PUT';
d.priv = OpenLayers.Request[e]({
url: a,
callback: this.createCallback(this.handleUpdate, d, b),
headers: b.headers,
data: this.format.write(c)
});
return d
},
handleUpdate: function (b, a) {
this.handleResponse(b, a)
},
'delete': function (d, c) {
c = c || {
};
var b = c.url || d.url || this.options.url + '/' + d.fid;
c = OpenLayers.Util.applyDefaults(c, this.options);
var e = new OpenLayers.Protocol.Response({
reqFeatures: d,
requestType: 'delete'
});
var f = this.deleteWithPOST ? 'POST' : 'DELETE';
var a = {
url: b,
callback: this.createCallback(this.handleDelete, e, c),
headers: c.headers
};
if (this.deleteWithPOST) {
a.data = this.format.write(d)
}
e.priv = OpenLayers.Request[f](a);
return e
},
handleDelete: function (b, a) {
this.handleResponse(b, a)
},
handleResponse: function (c, a) {
var b = c.priv;
if (a.callback) {
if (b.status >= 200 && b.status < 300) {
if (c.requestType != 'delete') {
c.features = this.parseFeatures(b)
}
c.code = OpenLayers.Protocol.Response.SUCCESS
} else {
c.code = OpenLayers.Protocol.Response.FAILURE
}
a.callback.call(a.scope, c)
}
},
parseFeatures: function (a) {
var b = a.responseXML;
if (!b || !b.documentElement) {
b = a.responseText
}
if (!b || b.length <= 0) {
return null
}
return this.format.read(b)
},
commit: function (b, q) {
q = OpenLayers.Util.applyDefaults(q, this.options);
var d = [
],
m = 0;
var k = {
};
k[OpenLayers.State.INSERT] = [
];
k[OpenLayers.State.UPDATE] = [
];
k[OpenLayers.State.DELETE] = [
];
var p,
l,
c = [
];
for (var e = 0, j = b.length; e < j; ++e) {
p = b[e];
l = k[p.state];
if (l) {
l.push(p);
c.push(p)
}
}
var g = (k[OpenLayers.State.INSERT].length > 0 ? 1 : 0) + k[OpenLayers.State.UPDATE].length + k[OpenLayers.State.DELETE].length;
var o = true;
var a = new OpenLayers.Protocol.Response({
reqFeatures: c
});
function h(s) {
var r = s.features ? s.features.length : 0;
var u = new Array(r);
for (var t = 0; t < r; ++t) {
u[t] = s.features[t].fid
}
a.insertIds = u;
n.apply(this, [
s
])
}
function n(i) {
this.callUserCallback(i, q);
o = o && i.success();
m++;
if (m >= g) {
if (q.callback) {
a.code = o ? OpenLayers.Protocol.Response.SUCCESS : OpenLayers.Protocol.Response.FAILURE;
q.callback.apply(q.scope, [
a
])
}
}
}
var f = k[OpenLayers.State.INSERT];
if (f.length > 0) {
d.push(this.create(f, OpenLayers.Util.applyDefaults({
callback: h,
scope: this
}, q.create)))
}
f = k[OpenLayers.State.UPDATE];
for (var e = f.length - 1; e >= 0; --e) {
d.push(this.update(f[e], OpenLayers.Util.applyDefaults({
callback: n,
scope: this
}, q.update)))
}
f = k[OpenLayers.State.DELETE];
for (var e = f.length - 1; e >= 0; --e) {
d.push(this['delete'](f[e], OpenLayers.Util.applyDefaults({
callback: n,
scope: this
}, q['delete'])))
}
return d
},
abort: function (a) {
if (a) {
a.priv.abort()
}
},
callUserCallback: function (c, a) {
var b = a[c.requestType];
if (b && b.callback) {
b.callback.call(b.scope, c)
}
},
CLASS_NAME: 'OpenLayers.Protocol.HTTP'
});
var GCUI = GCUI || {
};
GCUI.Layer = GCUI.Layer || {
};
GCUI.Layer.GeoConcept = OpenLayers.Class(OpenLayers.Layer.WMTS, {
isBaseLayer: true,
projection: new OpenLayers.Projection('EPSG:27572'),
maxExtent: new OpenLayers.Bounds(0, 0, 0, 0),
units: 'm',
tileSize: new OpenLayers.Size(300, 300),
numZoomLevels: 12,
format: 'image/png',
initialize: function (d, c, e, b) {
this.addOptions(e);
var a = {
name: d,
url: c,
params: e
};
a = OpenLayers.Util.applyDefaults(a, {
layer: e.layer,
style: e.tabname,
matrixSet: e.matrixSet
});
a = OpenLayers.Util.applyDefaults(a, b);
OpenLayers.Layer.WMTS.prototype.initialize.apply(this, [
a
]);
delete this.params.MAPNAME;
delete this.params.TABNAME;
this.initFormat();
this.url = this.getUrlGC() + '/wmts';
this.readMetadata();
this.checkMetadata()
},
initFormat: function () {
this.extension = (this.extension === 'jpeg') ? 'jpg' : this.extension;
if (this.extension === 'pngt') {
this.transparent = true
}
if (this.extension === 'png24') {
this.extension = 'png'
}
if (this.extension !== 'png' && this.extension !== 'jpg') {
this.extension = 'png'
}
this.format = 'image/' + this.extension
},
readMetadata: function () {
},
getUrlGC: function () {
var a = OpenLayers.Util.isArray(this.url) ? this.url[0] : this.url;
var b = a.lastIndexOf('/maps') !== - 1 ? a.lastIndexOf('/maps')  : a.lastIndexOf('/wmts');
return a.substring(0, b)
},
loadMetadata: function () {
var b = new OpenLayers.Protocol.Script({
url: this.getUrlGC() + '/api/lbs/layer/json',
format: new OpenLayers.Format()
});
var a = {
map: this.getMapName().replace('.gcm', ''),
tab: this.style,
tick: new Date().getTime()
};
if (this.layer) {
a = {
name: this.layer,
tick: new Date().getTime()
}
}
b.createRequest(b.url, a, OpenLayers.Function.bind(this.processMetadata, this))
},
writeMetadata: function (a) {
},
processMetadata: function (f) {
if (f && f.status === 'ERROR') {
GCUI.Console.error(this.CLASS_NAME + ' : ' + f.message);
return
}
var c = f.result;
this.writeMetadata(c);
var e = c.ratios;
var g = c.precision;
this.metadata.precision = g;
var d = c.extent;
this.layer = this.layer || c.name;
this.matrixSet = this.matrixSet || c.projection;
this.projection = new OpenLayers.Projection(c.projection);
this.mapname = c.map;
this.tabname = c.tab || c.tabname;
this.style = this.tabname;
OpenLayers.Util.applyDefaults(this.params, {
layerVersion: c.version
});
GCUI.Layer.GeoConcept.DATA[this.getMapName().replace('.gcm', '') + '.' + this.tabname] = f;
if (d && e) {
this.maxExtent = new OpenLayers.Bounds(g * d.minX, g * d.minY, g * d.maxX, g * d.maxY);
this.resolutions = [
];
for (var b = 0, a = e.length; b < a; b++) {
if ((b % 2) === 0) {
this.resolutions.push(g / parseFloat(e[b]))
}
}
} else {
this.resolutions = this.map.baseLayer.resolutions;
this.maxExtent = this.map.baseLayer.maxExtent
}
if (this.beforeInitLayer()) {
this.initLayer()
}
},
beforeInitLayer: function () {
if (this.singleTile && !this.userId) {
GCUI.getUserId((this.url instanceof Array) ? this.url[0] : this.url, this.getMapName(), OpenLayers.Function.bind(function (a) {
this.userId = a;
this.initLayer()
}, this));
return false
}
return true
},
initLayer: function () {
this.initialized = true;
this.addOptions({
resolutions: this.resolutions,
maxExtent: this.maxExtent,
tileFullExtent: this.maxExtent,
tileOrigin: new OpenLayers.LonLat(this.maxExtent.left, this.maxExtent.top)
});
if (this.map && this != this.map.baseLayer) {
this.redraw();
this.map.events.triggerEvent('changelayer', {
layer: this,
property: 'params'
})
}
this.events.triggerEvent('init')
},
setMap: function (a) {
OpenLayers.Layer.Grid.prototype.setMap.apply(this, [
a
])
},
checkMetadata: function () {
if (this.initialized) {
return
}
var a = GCUI.Layer.GeoConcept.DATA[this.getMapName().replace('.gcm', '') + '.' + this.tabname];
if (a) {
this.processMetadata(a)
} else {
if (this.visibility || this.initMapInformations) {
this.loadMetadata()
} else {
this.events.on({
visibilitychanged: this.checkMetadata,
scope: this
})
}
}
},
clone: function (a) {
if (a == null) {
a = new GCUI.Layer.GeoConcept(this.name, this.url, this.params, this.options)
}
a = OpenLayers.Layer.Grid.prototype.clone.apply(this, [
a
]);
return a
},
getMapName: function () {
var a = this.mapname || '';
if (a === '' && this.map && this.map.baseLayer) {
a = this.map.baseLayer.mapname || ''
}
if (this.singleTile && a !== '') {
a += ((a.indexOf('.gcm') === - 1) ? '.gcm' : '')
}
return a
},
getURL: function (c) {
if (!this.initialized) {
return OpenLayers.Util.getImagesLocation() + 'blank.gif'
}
if (!this.singleTile) {
return OpenLayers.Layer.WMTS.prototype.getURL.apply(this, [
c
])
} else {
var b = this.tileSize;
var g = this.map.getLogicalScale();
var a = this.url;
if (a instanceof Array) {
a = this.selectUrl(e, a)
}
a = a.substring(0, a.lastIndexOf('/wmts')) + '/gcservlet?';
var f = this.metadata.precision;
var d = c.left / f + ',' + c.bottom / f + ',' + c.right / f + ',' + c.top / f;
var e = [
'XgoAnswer=MapImage',
'XgoMapFile=' + this.getMapName(),
this.tabname ? 'XgoTabs=' + this.tabname : '',
'sizex=' + b.w,
'sizey=' + b.h,
'XgoSetLogicalScale=' + g,
'XgoSetViewBounds=' + d,
'XgoBitmapFormat=PNG',
'XgoNbBits=24',
'tr=' + (this.transparent ? '-1' : '0'),
'XgoTransparentBackGround=true',
'XgoSetViewBoundsExtraPixels=0',
'XgoSetViewBoundsFitUpperLS=false',
this.userId ? ('XgoUserID=' + this.userId)  : '',
'tick=' + new Date().getTime()
].join('&');
return a + e + (this.urlParams || '')
}
},
moveTo: function (b, a, c) {
if (!this.initialized) {
return
}
OpenLayers.Layer.WMTS.prototype.moveTo.apply(this, arguments)
},
CLASS_NAME: 'GCUI.Layer.GeoConcept'
});
GCUI.Layer.GeoConcept.DATA = [
];
GCUI.Layer = GCUI.Layer || {
};
GCUI.Layer.Object = OpenLayers.Class(OpenLayers.Layer, {
displayInLayerSwitcher: false,
initialize: function (a) {
OpenLayers.Layer.prototype.initialize.apply(this, [
'objects',
a
]);
this.objects = [
];
this.isIpad = (navigator.userAgent.match(/iPad/i) != null);
return this
},
setMap: function (a) {
OpenLayers.Layer.prototype.setMap.apply(this, [
a
]);
this.onAddLayerFunction = OpenLayers.Function.bind(function () {
this.setLayerIndex(this.objectLayer, this.layers.length)
}, this.map);
this.map.events.register('addlayer', this.map, this.onAddLayerFunction);
this.div.className = 'mapobjects';
this.div.id = 'mapobjects';
this.dirty = false
},
removeMap: function (a) {
this.map.events.unregister('addlayer', this.map, this.onAddLayerFunction);
this.onAddLayerFunction = null;
this.map.objectLayer = null
},
remove: function (a) {
this.div.parentNode.removeChild(this.div)
},
getInfoEvent: function () {
return (this.isIpad ? 'ontouchend' : 'onclick')
},
create: function (s) {
var c = 0;
var q = null;
var p = 'mapobjectname';
var a = this.objects.length;
var k = this.map.div.id;
var n = '<div id="' + k + '_obj_';
var t = this.getInfoEvent();
var m = '" ';
var l = ' style="position:absolute;" class="mapobject" > </div>';
var j = ' style="position:absolute;" class="mapobject" >';
var i = '</div>';
var h = '<img src="';
var g = '" id="' + k + '_obj_';
var f = '" class="mapobject" ';
var u = '<div class="';
var r = '" id="' + k + '_objname_';
var e = ' >';
var v = [
];
var o;
for (c = 0; c < a; c++) {
q = this.objects[c];
if (q.type) {
v.push(n);
v.push(c);
v.push(m);
v.push(l)
} else {
if (q.innerHTML) {
v.push(n);
v.push(c);
v.push(m);
v.push(j);
v.push(q.innerHTML);
v.push(i)
} else {
v.push(h);
v.push(q.imgsrc);
v.push(g);
v.push(c);
v.push(f);
v.push(' />')
}
}
if (q.name) {
if (q.objnamecss) {
p = q.objnamecss
}
o = '';
if (q.nameBackgroundColor) {
o = 'style=background-color:' + q.nameBackgroundColor + ';'
}
v.push(u);
v.push(p);
v.push(r);
v.push(c);
v.push(m);
v.push(o);
v.push(e);
v.push(q.name);
v.push(i)
}
}
s.innerHTML = v.join('');
var d = k + '_obj_';
var b = k + '_objname_';
var w = document;
for (c = 0; c < a; c++) {
q = this.objects[c];
q.mainDiv = w.getElementById(d + c);
q.mainDiv[t] = OpenLayers.Function.bindAsEventListener(function (x) {
OpenLayers.Event.stop(x);
DynMapShowObjectSheet(DynMapGetMap(document, k), this.id)
}, q);
if (q.name) {
q.nameDiv = w.getElementById(b + c);
q.nameDiv[t] = OpenLayers.Function.bindAsEventListener(function (x) {
OpenLayers.Event.stop(x);
DynMapShowObjectSheet(DynMapGetMap(document, k), this.id)
}, q)
}
}
this.dirty = false
},
moveObject: function (b, d) {
var c = this.findObject(b.id);
if (!c) {
return false
}
c.text = b.text;
c.mapx = parseFloat(b.mapx);
c.mapy = parseFloat(b.mapy);
var a = (c.innerHTML !== null && c.innerHTML != b.innerHTML);
if (a) {
c.innerHTML = b.innerHTML
}
if (d) {
c.mapx /= this.map.precision;
c.mapy /= this.map.precision
}
if (!this.map.dragging && !this.map.scrolling && c.mainDiv) {
this.updateObject(c, a)
}
return true
},
moveObjects: function (e, d) {
var c;
var b = true;
var a = e.length;
for (c = 0; c < a;
c++) {
b = b && this.moveObject(e[c], d)
}
return b
},
addObject: function (a, c) {
var b = {
};
if (a.id !== null) {
b.id = a.id
} else {
b.id = a.name
}
b.mapx = parseFloat(a.mapx);
b.mapy = parseFloat(a.mapy);
b.name = a.name;
b.text = a.text;
b.deltaX = a.deltaX;
b.deltaY = a.deltaY;
b.imgsrc = a.imgsrc;
b.width = a.width;
b.innerHTML = a.innerHTML;
b.type = a.type;
b.objnamecss = a.objnamecss;
b.nameBackgroundColor = a.nameBackgroundColor;
if (a.visMinScale) {
b.visMinScale = a.visMinScale
}
if (a.visMaxScale) {
b.visMaxScale = a.visMaxScale
}
if (c) {
b.mapx /= this.map.precision;
b.mapy /= this.map.precision
}
this.objects.push(b);
this.dirty = true
},
removeObject: function (d) {
var c = [
];
var e = this.objects;
var b;
var a = e.length;
for (b = 0; b < a; b++) {
if (e[b].id !== d) {
c.push(e[b])
}
}
this.objects = c;
this.dirty = true
},
getNumObject: function (b) {
var d,
a,
c;
if (b) {
d = this.objects;
c = d.length;
for (a = 0; a < c; a++) {
if (d[a].id == b) {
return a
}
}
}
},
getObjectXY: function (b, e) {
var d = this.findObject(b);
var c = [
];
var a,
f;
if (d) {
a = e ? d.mapx * this.map.precision : d.mapx;
f = e ? d.mapy * this.map.precision : d.mapy;
c.push(a);
c.push(f)
}
return c
},
setObjectNameCss: function (a, b) {
var c = this.findObject(a);
if (!c) {
return
}
c.objnamecss = b;
c.nameDiv.className = b
},
setObjectDivCss: function (b, a) {
var c = this.findObject(b);
c.mainDiv.className = a
},
addObjects: function (d, c) {
var b;
var a = d.length;
for (b = 0;
b < a; b++) {
this.addObject(d[b], c)
}
},
getCSS: function (b, a) {
var d = '';
var c;
if (!b) {
return ''
}
if (b.currentStyle) {
c = a.replace(/\-(.)/g, function (e, f) {
return f.toUpperCase()
});
d = b.currentStyle[c]
} else {
if (window.getComputedStyle) {
d = document.defaultView.getComputedStyle(b, null).getPropertyValue(a)
}
}
return d
},
_getObjImgSrc: function (a) {
for (var b = 0;
b < a.childNodes.length; b++) {
if (a.childNodes[b].src) {
return a.childNodes[b].src
} else {
return (this._getObjImgSrc(a.childNodes[b]))
}
}
},
transformLabel: function (a) {
return a
},
toJSON: function () {
var s,
o,
t,
g,
j,
c,
k,
m,
b,
a,
x,
v,
l,
h;
var e = this.objects.length;
var u = '';
var q = this.map.precision;
var f = this.map.div.id + '_obj_';
var d = this.map.div.id + '_objname_';
var r = this.getCSS;
var w = document;
var n = this.map.getExtent();
for (s = 0; s < e; s++) {
o = this.objects[s];
if (n.containsLonLat(new OpenLayers.LonLat(o.mapx * q, o.mapy * q))) {
j = '{"text":"' + this.transformLabel(o.name) + '",';
l = w.getElementById(d + s);
j += '"bgcol":"' + r(l, 'background-color') + '",';
j += '"color":"' + r(l, 'color') + '",';
j += '"font":"' + r(l, 'font-family') + '",';
j += '"fontsize":"' + r(l, 'font-size') + '",';
j += '"hotspot":[0,0],"delta":[' + (8 - o.deltaX) + ',' + ( - 32 - o.deltaY) + ']}';
u += '{"type":"point","center":[' + o.mapx * q + ',' + o.mapy * q + ']';
if (o.type) {
t = o.type.getStyle(DynMapGetScale(this.map));
if (t.icon) {
h = w.getElementById(f + s);
g = r(h, 'background-image');
g = g.replace('url(', '').replace(')', '').replace('"', '').replace('"', '');
u += ',"style":{"type":"image","url":"' + g + '"}'
} else {
c = t.width;
k = t.height;
b = '[0,0]';
a = '[' + c + ',0]';
x = '[' + c + ',' + k + ']';
v = '[0,' + k + ']';
m = '[' + b + ',' + a + ',' + x + ',' + v + ',' + b + ']';
u += ',"style":{"type":"polygon","polygon":' + m + ',"lineColor":"' + t.borderColor + '","lineWidth":' + t.borderWidth + ',"opacity":' + (t.bgOpacity * 100) + ',"order":0,"fillColor":"' + t.bgColor + '"}'
}
u += ',"hotspot":[' + t.hotSpotX + ',' + t.hotSpotY
} else {
g = w.getElementById(f + s).src;
if (!g) {
g = this._getObjImgSrc(o.mainDiv)
}
u += ',"label":' + j + ',"style":{"type":"image","url":"' + g + '"}';
u += ',"hotspot":[' + ( - o.deltaX) + ',' + ( - o.deltaY)
}
u += '],"order":' + s + '},'
}
}
if (u !== '') {
u = u.substring(0, u.length - 1)
}
return u
},
clearObjects: function () {
this.objects.length = 0;
this.dirty = true
},
findObject: function (a) {
var e = null;
var d,
b,
c;
if (a) {
d = this.objects;
b = 0;
c = d.length;
for (b = 0; b < c; b++) {
if (d[b].id == a) {
e = d[b];
break
}
}
}
return e
},
setDragMode: function (a) {
this.dragMode = a
},
moveTo: function (b, a, c) {
OpenLayers.Layer.prototype.moveTo.apply(this, [
b,
a,
c
]);
this.divLeft = parseInt(this.map.layerContainerDiv.style.left);
this.divTop = parseInt(this.map.layerContainerDiv.style.top);
this.div.style.left = - this.divLeft + 'px';
this.div.style.top = - this.divTop + 'px';
this.refresh();
this.drawn = true
},
refresh: function (c) {
if (this.updating || !this.getVisibility()) {
return
}
this.updating = true;
if (this.dirty) {
this.create(this.div);
c = true
}
var a = 0;
var b = this.objects.length;
if (this.multiLabels) {
this.nbObjsInPosXY = [
]
}
for (a = 0; a < b; a++) {
this.updateObject(this.objects[a], c)
}
this.updating = false
},
displayObj: function (b, a) {
b.mainDiv.style.display = a;
if (b.nameDiv) {
this.displayName(b, a)
}
},
displayName: function (b, a) {
b.nameDiv.style.display = a
},
updateObject: function (f, a) {
if (!f.mainDiv) {
return
}
f.posx = DynMapCalcPixelX(this.map, f.mapx);
f.posy = DynMapCalcPixelY(this.map, f.mapy);
if (this.multiLabels) {
var g = f.posx + '_' + f.posy;
if (!this.nbObjsInPosXY[g]) {
this.nbObjsInPosXY[g] = 1
} else {
this.nbObjsInPosXY[g] = this.nbObjsInPosXY[g] + 1
}
}
var b = true;
var e = 0;
var h,
c,
j,
i;
if ((f.posx > this.map.size.w + e) || (f.posx <= - e) || (f.posy > this.map.size.h + e) || (f.posy <= - e)) {
b = false
}
if (b) {
if (!f.visible && f.mainDiv) {
this.displayObj(f, 'block');
f.visible = true
}
if (f.type) {
this.updateObjectStyle(f, a)
} else {
if (a && f.innerHTML && !this.dragMode) {
f.mainDiv.innerHTML = f.innerHTML
}
j = (f.mainDiv && f.mainDiv.width) ? - f.mainDiv.width / 2 : 0;
i = (f.mainDiv && f.mainDiv.height) ? - f.mainDiv.height / 2 : 0;
if (!f.deltaX) {
f.deltaX = j
}
if (!f.deltaY) {
f.deltaY = i
}
f.mainDiv.style.left = (f.posx + f.deltaX) + 'px';
f.mainDiv.style.top = (f.posy + f.deltaY) + 'px';
var d = DynMapGetScale(this.map);
if (f.visMinScale) {
if (f.visMinScale <= d && f.visMaxScale > d) {
this.displayObj(f, 'block')
} else {
this.displayObj(f, 'none')
}
}
if (f.nameDiv) {
if (this.multiLabels) {
h = this.nbObjsInPosXY[f.posx + '_' + f.posy];
c = f.nameDiv.clientWidth;
f.nameDiv.style.left = (f.posx - f.deltaX + (Math.floor(h / 9)) * c + c * this.deltaPosNameX[(h - 1) % 8]) + 'px';
f.nameDiv.style.top = (f.posy + f.deltaY + (Math.floor(h / 9)) * c - this.deltaPosNameY[(h - 1) % 8]) + 'px'
} else {
f.nameDiv.style.left = (f.posx + (j === 0 ? 0 : (f.deltaX - j)) + 8) + 'px';
f.nameDiv.style.top = (f.posy + (i === 0 ? 0 : (f.deltaY - i)) - 32) + 'px'
}
if (this.namedivminscale) {
if (this.namedivminscale <= d && this.namedivmaxscale > d) {
  this.displayName(f, 'block')
} else {
  this.displayName(f, 'none')
}
}
}
}
} else {
if (f.visible) {
this.displayObj(f, 'none');
f.visible = false
}
}
},
setNameDivVisibilityRange: function (a, b) {
this.namedivminscale = a;
this.namedivmaxscale = b
},
activateMultiLabels: function () {
this.multiLabels = true;
this.nbObjsInPosXY = [
];
this.deltaPosNameX = [
0,
0,
0,
- 1,
- 2,
- 2,
- 2,
- 1
];
this.deltaPosNameY = [
22,
5,
- 12,
- 12,
- 12,
5,
22,
22
]
},
updateObjectStyle: function (f, c) {
var a,
d;
var h = f.type;
var m,
l,
k,
j,
i,
b,
g;
if (h && f.mainDiv) {
var e = DynMapGetScale(this.map);
d = f.mainDiv.style;
a = h.getStyle(e);
m = a ? a.hotSpotX : f.mainDiv.width / 2;
l = a ? a.hotSpotY : f.mainDiv.height / 2;
d.left = (f.posx - m) + 'px';
d.top = (f.posy - l) + 'px';
if (c || a != f.currentStyle) {
f.currentStyle = a;
d.width = a.width + 'px';
d.height = a.height + 'px';
d.padding = 0;
d.margin = 0;
if (a.bgColor) {
d.backgroundColor = a.bgColor
} else {
d.backgroundColor = 'transparent'
}
if (a.borderColor && a.borderWidth) {
d.borderColor = a.borderColor;
d.borderStyle = 'solid';
d.borderWidth = a.borderWidth
} else {
d.borderStyle = 'none'
}
d.opacity = a.bgOpacity;
if (a.icon) {
d.backgroundImage = 'url(' + a.icon + ')'
} else {
d.backgroundImage = 'none'
}
}
if (f.nameDiv) {
if (a.objnamecss) {
this.displayName(f, 'block');
if (this.multiLabels) {
k = this.nbObjsInPosXY[f.posx + '_' + f.posy];
if (!f.nameW) {
  f.nameW = f.nameDiv.clientWidth
}
b = Math.floor(k / 9) * f.nameW;
g = (k - 1) % 8;
f.nameDiv.style.left = (f.posx - f.deltaX + b + f.nameW * this.deltaPosNameX[g]) + 'px';
f.nameDiv.style.top = (f.posy + f.deltaY + b - this.deltaPosNameY[g]) + 'px'
} else {
j = a.width / 2 - a.hotSpotX;
i = a.height / 2 - a.hotSpotY;
f.nameDiv.style.left = (f.posx + j + 8) + 'px';
f.nameDiv.style.top = (f.posy + i - 32) + 'px'
}
f.nameDiv.className = a.objnamecss;
if (this.namedivminscale) {
if (this.namedivminscale <= e && this.namedivmaxscale > e) {
  this.displayName(f, 'block')
} else {
  this.displayName(f, 'none')
}
}
} else {
this.displayName(f, 'none')
}
}
}
},
getDataExtent: function () {
var b = null;
var e = this.objects;
if (e && (e.length > 0)) {
b = new OpenLayers.Bounds();
var g = null;
var c = this.map.precision;
for (var d = 0, a = e.length; d < a; d++) {
var f = e[d];
g = new OpenLayers.Geometry.Point(f.mapx * c, f.mapy * c);
if (g) {
b.extend(g.getBounds())
}
}
}
return b
},
CLASS_NAME: 'GCUI.Layer.Object'
});
function GCISObjectType(a, b) {
this.name = a;
this.styles = b;
this.scaleStyles = [
]
}
GCISObjectType.prototype.getStyle = function (c) {
var a;
var b = this.scaleStyles[c];
if (!b) {
for (a = 0; a < this.styles.length; a++) {
b = this.styles[a];
if (b.minScale <= c && b.maxScale >= c) {
this.scaleStyles[c] = b;
break
}
}
}
return b
};
function GCISObjectStyle(e, a, j, b, k, i, h, l, f, g, d, c) {
this.minScale = e;
this.maxScale = a;
this.icon = j;
this.width = b;
this.height = k;
this.hotSpotX = i;
this.hotSpotY = h;
this.bgColor = l;
this.bgOpacity = f;
this.borderColor = g;
this.borderWidth = d;
this.objnamecss = c
}
GCUI.Map = OpenLayers.Class(OpenLayers.Map, {
EVENT_TYPES: [
'preaddlayer',
'addlayer',
'preremovelayer',
'removelayer',
'changelayer',
'movestart',
'move',
'moveend',
'zoomend',
'popupopen',
'popupclose',
'addmarker',
'removemarker',
'clearmarkers',
'mouseover',
'mouseout',
'mousemove',
'dragstart',
'drag',
'dragend',
'changebaselayer',
'load'
],
allOverlays: true,
projection: 'EPSG:27572',
minLogicalScale: 1,
maxLogicalScale: 12,
numZoomLevels: 12,
initialize: function (b, a) {
if (a) {
if (a.x && a.y && !a.center) {
a.center = new OpenLayers.LonLat(a.x, a.y)
}
a.theme = null;
a.controls = a.controls || this.getDefaultControls();
if (a.scale) {
a.zoom = this.numZoomLevels - a.scale
}
}
OpenLayers.Map.prototype.initialize.apply(this, [
b,
a
]);
this.initBaseLayer(b, a)
},
getDefaultControls: function () {
return [new GCUI.Control.Navigation()]
},
initBaseLayer: function (e, a) {
this.addToDocument(e);
if (!(this.server && ((this.mapName && this.tab) || this.layer))) {
return
}
var c = null;
if (this.limits) {
c = {
minX: this.limits[0],
maxX: this.limits[1],
minY: this.limits[2],
maxY: this.limits[3]
}
}
this.events.register('load', this, this.addDefaultHtcLayers);
this.showSlider = this.showSlider || (a.showSlider === undefined);
var d = this.ratios ? this.ratios.split('~')  : null;
var b = new GCUI.Layer.GeoConcept('main', this.server, {
mapname: this.mapName,
tabname: this.tab,
layer: this.layer
}, OpenLayers.Util.extend({
extension: this.format,
mapversion: (this.version ? this.version : 0),
tileSize: new OpenLayers.Size(this.tilewidth || 300, this.tileheight || 300),
precision: this.precision,
ratios: d,
extent: c,
initMapInformations: true
}, a.layerOptions));
if (this.ratios) {
this.onBaseLayerLoaded({
object: b
})
} else {
b.events.on({
init: this.onBaseLayerLoaded,
scope: this
})
}
},
onBaseLayerLoaded: function (c) {
var a = c.object;
if (a.ratios && a.extent) {
a.processMetadata({
result: a
})
}
this.maxLogicalScale = this.options.maxLogicalScale || a.resolutions.length;
this.resolution = a.resolutions[this.maxLogicalScale - this.zoom - 1];
this.addLayer(a);
var b = this.getCenter();
if (!b || (b.lon === 0 && b.lat === 0)) {
b = a.maxExtent.getCenterLonLat()
}
this.precision = a.metadata.precision;
this.maxExtent = a.getMaxExtent();
this.moveTo(b, this.zoom, {
forceZoomChange: true
});
if (!this.initialized) {
this.initialized = true;
this.events.triggerEvent('load')
}
},
addToDocument: function (b) {
var a = this.document || document;
if (!a.maps) {
a.maps = [
]
}
a.maps[b] = this
},
addDefaultHtcLayers: function () {
this.objectLayer = new GCUI.Layer.Object();
this.addLayer(this.objectLayer);
this.objectLayer.resolutions = this.baseLayer.resolutions;
var a = this.scale || this.getLogicalScale();
this.setCenter(null, this.getNumZoomLevels() - a);
this.setLayerIndex(this.objectLayer, this.layers.length);
if (this.showSlider) {
delete this.showSlider;
this.addControl(new GCUI.Control.ScaleSlider())
}
},
isValidZoomLevel: function (b) {
var a = this.getNumZoomLevels() - b;
return ((b !== null) && (a >= this.minLogicalScale) && (a <= this.maxLogicalScale))
},
getLogicalScale: function () {
return (this.getNumZoomLevels() - this.zoom)
},
animateZoom: function (c) {
if (!c || (c < this.minLogicalScale) || (c > this.maxLogicalScale) || (c === this.getLogicalScale())) {
return
}
var b = this.getSize();
var a = new OpenLayers.Pixel(b.w / 2, b.h / 2);
this.zoomTo(this.getNumZoomLevels() - c, a)
},
onEvent: function (b, a) {
this.events.register(b, this, a);
if (b === 'load' && this.initialized) {
a()
}
},
maximize: function (e, b) {
var j = this.document || document;
var f = this.window || window;
var g = j.documentElement;
var c = g.clientWidth ? g.clientWidth : j.body.clientWidth;
var k = (f.innerWidth ? f.innerWidth : c);
var a = g.clientHeight ? g.clientHeight : j.body.clientHeight;
var d = (f.innerHeight ? f.innerHeight : a);
var i = OpenLayers.Util.pagePosition(this.div);
this.div.style.width = (k - i[0] - (e || 0)) + 'px';
this.div.style.height = (d - i[1] - (b || 0)) + 'px';
this.updateSize()
},
setMaxLogicalScale: function (b) {
this.maxLogicalScale = b;
var c = this.getControlsByClass('GCUI.Control.ScaleSlider');
for (var a = 0; a < c.length; a++) {
c[a].setMaximumScale(b)
}
if (this.baseLayer && this.getLogicalScale() > b) {
this.zoomTo(this.getNumZoomLevels() - b)
}
},
setMinLogicalScale: function (b) {
this.minLogicalScale = b;
var c = this.getControlsByClass('GCUI.Control.ScaleSlider');
for (var a = 0; a < c.length; a++) {
c[a].setMinimumScale(b)
}
if (this.baseLayer && this.getLogicalScale() < b) {
this.zoomTo(this.getNumZoomLevels() - b)
}
},
setSize: function (a, b) {
this.div.style.width = a;
this.div.style.height = b;
this.updateSize()
},
toJSON: function (m) {
var w = '{ "context":{';
var l = this.precision;
var t = m ? m : 'png';
var c = this.getLonLatFromPixel(new OpenLayers.Pixel(0, 0));
var b = this.getLonLatFromPixel(new OpenLayers.Pixel(this.size.w, this.size.h));
var a = '[[' + c.lon + ',' + b.lat + '],[' + b.lon + ',' + c.lat + ']]';
var y = this.getCenter();
w += '"center":[' + y.lon + ',' + y.lat;
w += '],"size":[' + this.size.w + ',' + this.size.h;
w += '],"scale":' + this.getLogicalScale() + ',"bbox": ' + a;
w += ',"format": "' + t + '"';
var u = this.getControlsByClass('GCUI.Control.GraphicScale');
if (u && u[0]) {
w += ',"graphicScale":' + u[0].toJSON()
}
w += '},"layers": [';
var r;
var e,
q = 1;
for (r = 0; r < this.layers.length; r++) {
e = this.layers[r];
if (e.getVisibility() && e.CLASS_NAME === 'GCUI.Layer.GeoConcept') {
t = e.extension;
if (t === 'png' && e.transparent) {
t = 'pngt'
}
var h = e.getMapName();
if (h.indexOf('.gcm') === - 1) {
h = h + '.gcm'
}
var d = e.tabname || '';
w += '{"order":' + q + ',"type":"raster","raster":{"map":"' + h + '","tab":"' + d + '","format":"' + t + '","opacity":' + (e.opacity * 100 || 100) + (e.userId ? (',"userId":"' + e.userId + '"')  : '') + '}},';
q++
}
}
w += '{"order":' + q + ',"type":"vector","features": [';
var g = this.objectLayer.getVisibility() ? this.objectLayer.toJSON()  : '';
if (this.copyrightLayer) {
var x = this.copyrightLayer.toJSON();
if (g !== '' && x !== '') {
g += ','
}
g += x
}
w += g;
var n = '';
var v = 0;
for (r = 0; r < this.layers.length; r++) {
e = this.layers[r];
if (e.getVisibility() && e.CLASS_NAME === 'OpenLayers.Layer.Vector') {
var o;
var s = e.features.length;
for (o = 0; o < s; o++) {
var f = e.features[o];
if (f.onScreen()) {
var k = GCUI.Util.getFeatureJSON(f, l, v);
v++;
n += k;
if (k !== '') {
  n += ','
}
}
}
}
}
if (n !== '') {
n = (g !== '' ? ',' : '') + n.substring(0, n.length - 1)
}
w += n;
return w + ']}]}'
},
moveTo: function (h, b, e) {
if (h != null && !(h instanceof OpenLayers.LonLat)) {
h = new OpenLayers.LonLat(h)
}
if (!e) {
e = {
}
}
if (b != null) {
b = parseFloat(b);
if (!this.fractionalZoom) {
b = Math.round(b)
}
}
var m = b;
b = this.adjustZoom(b);
if (b !== m) {
h = this.getCenter()
}
var p = e.dragging || this.dragging;
var k = e.forceZoomChange;
if (!this.getCachedCenter() && !this.isValidLonLat(h)) {
h = this.maxExtent.getCenterLonLat();
this.center = h.clone()
}
if (this.restrictedExtent != null) {
if (h == null) {
h = this.center
}
if (b == null) {
b = this.getZoom()
}
var q = this.getResolutionForZoom(b);
var n = this.calculateBounds(h, q);
if (!this.restrictedExtent.containsBounds(n)) {
var w = this.restrictedExtent.getCenterLonLat();
if (n.getWidth() > this.restrictedExtent.getWidth()) {
h = new OpenLayers.LonLat(w.lon, h.lat)
} else {
if (n.left < this.restrictedExtent.left) {
h = h.add(this.restrictedExtent.left - n.left, 0)
} else {
if (n.right > this.restrictedExtent.right) {
  h = h.add(this.restrictedExtent.right - n.right, 0)
}
}
}
if (n.getHeight() > this.restrictedExtent.getHeight()) {
h = new OpenLayers.LonLat(h.lon, w.lat)
} else {
if (n.bottom < this.restrictedExtent.bottom) {
h = h.add(0, this.restrictedExtent.bottom - n.bottom)
} else {
if (n.top > this.restrictedExtent.top) {
  h = h.add(0, this.restrictedExtent.top - n.top)
}
}
}
}
}
var l = k || ((this.isValidZoomLevel(b)) && (b != this.getZoom()));
var g = (this.isValidLonLat(h)) && (!h.equals(this.center));
if (l || g || p) {
p || this.events.triggerEvent('movestart', {
zoomChanged: l
});
if (g) {
if (!l && this.center) {
this.centerLayerContainer(h)
}
this.center = h.clone()
}
var x = l ? this.getResolutionForZoom(b)  : this.getResolution();
if (l || this.layerContainerOrigin == null) {
this.layerContainerOrigin = this.getCachedCenter();
this.layerContainerOriginPx.x = 0;
this.layerContainerOriginPx.y = 0;
this.applyTransform();
var o = this.getMaxExtent({
restricted: true
});
var d = o.getCenterLonLat();
var j = this.center.lon - d.lon;
var c = d.lat - this.center.lat;
var u = o.getWidth() / x;
var t = o.getHeight() / x;
this.minPx = {
x: (this.size.w - u) / 2 - j / x,
y: (this.size.h - t) / 2 - c / x
};
this.maxPx = {
x: this.minPx.x + u,
y: this.minPx.y + t
}
}
if (l) {
this.zoom = b;
this.resolution = x
}
var f = this.getExtent();
if (this.baseLayer.visibility && this.baseLayer.opacity !== 0) {
this.baseLayer.moveTo(f, l, e.dragging);
e.dragging || this.baseLayer.events.triggerEvent('moveend', {
zoomChanged: l
})
}
f = this.baseLayer.getExtent();
for (var r = this.layers.length - 1; r >= 0; --r) {
var v = this.layers[r];
if (v !== this.baseLayer && !v.isBaseLayer) {
var a = v.calculateInRange();
if (v.inRange != a) {
v.inRange = a;
if (!a) {
  v.display(false)
}
this.events.triggerEvent('changelayer', {
  layer: v,
  property: 'visibility'
})
}
if (a && v.visibility && v.opacity !== 0) {
v.moveTo(f, l, e.dragging);
e.dragging || v.events.triggerEvent('moveend', {
  zoomChanged: l
})
}
}
}
this.events.triggerEvent('move');
p || this.events.triggerEvent('moveend');
if (l) {
for (var r = 0, s = this.popups.length; r < s; r++) {
this.popups[r].updatePosition()
}
this.events.triggerEvent('zoomend')
}
}
},
centerLayerContainer: function (c) {
var d = this.getViewPortPxFromLonLat(this.layerContainerOrigin);
var g = this.getViewPortPxFromLonLat(c);
if ((d != null) && (g != null)) {
var a = this.layerContainerOriginPx.x;
var b = this.layerContainerOriginPx.y;
var f = d.x - g.x;
var e = d.y - g.y;
this.applyTransform((this.layerContainerOriginPx.x = f), (this.layerContainerOriginPx.y = e));
var i = a - f;
var h = b - e;
this.minPx.x -= i;
this.maxPx.x -= i;
this.minPx.y -= h;
this.maxPx.y -= h
}
},
CLASS_NAME: 'GCUI.Map'
});
GCUI.getMap = function (a, c) {
if (!c) {
c = document
}
if (!c.maps) {
return null
}
if (!a) {
for (var b in c.maps) {
if (c.maps.hasOwnProperty(b)) {
a = b;
break
}
}
}
return c.maps[a] || null
};
GCUI.getUserId = function (c, b, e) {
var a = c.lastIndexOf('/map');
if (a === - 1) {
a = c.lastIndexOf('/wmts')
}
c = c.substring(0, a);
var d = new OpenLayers.Protocol.Script({
url: c + '/htcservlet/gcis.js',
callbackKey: 'C'
});
if (b.indexOf('.gcm') === - 1) {
b = b + '.gcm'
}
d.createRequest(d.url, {
M: b,
V: 'XgoUserID',
T: new Date().getTime()
}, function (f) {
e(f.responseText)
})
};
GCUI.Util = GCUI.Util || {
};
GCUI.Util.getFeatureJSON = function (h, o, k) {
if (!h.kind && h.geometry) {
if (h.geometry instanceof OpenLayers.Geometry.LineString) {
h.kind = 'line'
}
if (h.geometry instanceof OpenLayers.Geometry.Polygon || h.geometry instanceof OpenLayers.Geometry.MultiPolygon) {
h.kind = 'poly'
}
}
var m = h.style || h.layer.styleMap.styles['default'].defaultStyle;
var a = 100 * (m.strokeOpacity || 1);
if (h.kind === 'circle') {
return '{"type":"circle","center":[' + GCUI.Util.getFeatureXpoints(h) [0] * o + ',' + GCUI.Util.getFeatureYpoints(h) [0] * o + '],"radius":' + GCUI.Util.getFeatureRadius(h) * o + ',"lineColor":"' + m.strokeColor + '","lineWidth":' + m.strokeWidth + ',"opacity":' + a + ',"order":' + k + ',"fillColor":"' + m.fillColor + '","fillOpacity":' + (100 * m.fillOpacity) + '}'
}
if (h.kind === 'line' || h.kind === 'poly' || h.kind === 'rect') {
var l,
f,
e;
var q = [
];
var n = GCUI.Util.getFeatureXpoints(h);
var s = GCUI.Util.getFeatureYpoints(h);
var g = n.length;
if (h.kind === 'poly' && g < 3) {
return ''
}
for (l = 0; l < g; l++) {
f = n[l];
e = s[l];
q.push('[');
q.push(f * o);
q.push(',');
q.push(e * o);
q.push(']');
if (l !== g - 1) {
q.push(',')
}
}
q.push('],');
var c = '{ "type":"line","linestring":[' + q.join('');
var p = '}';
if (h.kind === 'poly' || h.kind === 'rect') {
c = '{"type":"polygon","polygon":"' + GCUI.Util.getFeatureWkt(h, true) + '",';
p = ',"fillColor":"' + m.fillColor + '","fillOpacity":' + (100 * m.fillOpacity) + '}'
}
return c + '"lineColor":"' + m.strokeColor + '","lineWidth":' + m.strokeWidth + ',"opacity":' + a + ',"order":' + k + p
} else {
var r = h.layer.map;
q = '';
if (h.style && h.style.externalGraphic) {
q = '{"type":"point","center":[';
if (r.objectLayer.getObjectXY(h.fid).length > 0) {
var j = r.objectLayer.getObjectXY(h.fid);
q += j[0] * o + ',' + j[1] * o + ']';
var d = '{"text":"' + h.style.label + '",';
d += '"bgcol":"' + h.style.fontColor + '",';
d += '"color":"#FFFFFF",';
d += '"font":"' + h.style.fontFamily + '",';
d += '"fontsize":"' + h.style.fontSize + '",';
d += '"hotspot":[0,0],"delta":[' + ( - h.style.labelXOffset) + ',' + ( - h.style.labelYOffset) + ']}';
q += ',"label":' + d
} else {
q += h.geometry.x + ',' + h.geometry.y + ']'
}
var b = OpenLayers.Util.createUrlObject(h.style.externalGraphic);
if (b.protocol === 'http:') {
b = b.protocol + '//' + b.host + ':' + b.port + b.pathname + '?' + OpenLayers.Util.getParameterString(b.args)
}
q += ',"style":{"type":"image","url":"' + b + '"}';
if (h.style.graphicXOffset != undefined && h.style.graphicYOffset != undefined) {
q += ',"hotspot":[' + ( - h.style.graphicXOffset) + ',' + ( - h.style.graphicYOffset) + ']'
} else {
if (h.style.graphicWidth && h.style.graphicHeight) {
q += ',"hotspot":[' + (h.style.graphicWidth / 2) + ',' + (h.style.graphicHeight / 2) + ']'
}
}
q += ',"order":' + k + '}'
}
return q
}
};
GCUI.Util.getFeatureXpoints = function (f, g) {
var b = [
];
var c = g ? 1 : f.layer.map.precision;
if (f.kind === 'circle') {
b.push(f.geometry.getBounds().getCenterLonLat().lon / c)
} else {
var d = f.geometry.getVertices();
for (var e = 0, a = d.length; e < a; e++) {
b.push(d[e].x / c)
}
if (f.kind === 'poly' && b.length > 0) {
b.push(b[0])
}
}
if (f.kind === 'rect') {
b[1] = b[0];
b[3] = b[2];
b[4] = b[0]
}
return b
};
GCUI.Util.getFeatureYpoints = function (f, g) {
var b = [
];
var c = g ? 1 : f.layer.map.precision;
if (f.kind === 'circle') {
b.push(f.geometry.getBounds().getCenterLonLat().lat / c)
} else {
var d = f.geometry.getVertices();
for (var e = 0, a = d.length; e < a; e++) {
b.push(d[e].y / c)
}
if (f.kind === 'poly' && b.length > 0) {
b.push(b[0])
}
}
if (f.kind === 'rect') {
b[1] = b[2];
b[3] = b[0];
b[4] = b[0]
}
return b
};
GCUI.Util.getFeatureRadius = function (c, d) {
var b = c.geometry.getBounds();
var a = d ? 1 : c.layer.map.precision;
return b.getWidth() / (2 * a)
};
GCUI.Util.getFeatureWkt = function (e, b) {
if (!b) {
b = e.layer.map.precision;
e = e.clone();
var c = e.geometry.getVertices();
for (var d = 0, a = c.length; d < a; d++) {
c[d].x = c[d].x / b;
c[d].y = c[d].y / b
}
}
return (new OpenLayers.Format.WKT()).write(e)
};
GCUI.Console = GCUI.Console || {
};
GCUI.Console.error = function (b) {
try {
if (window.console && console.error) {
console.error(b)
}
} catch (a) {
}
};
OpenLayers.Util.extend(OpenLayers.Console, GCUI.Console);
GCUI.Control = GCUI.Control || {
};
GCUI.Control.Navigation = OpenLayers.Class(OpenLayers.Control.Navigation, {
centerOnWheelEvent: false,
mouseWheelOptions: {
interval: 50
},
defaultDblClick: function (b) {
var a = this.map.getLonLatFromViewPortPx(b.xy);
this.map.panTo(a)
},
wheelChange: function (a, f) {
var e = this.map.getZoom();
f = (f > 0) ? 1 : - 1;
var d = this.map.getZoom() + Math.round(f);
d = Math.max(d, this.map.getNumZoomLevels() - this.map.maxLogicalScale);
d = Math.min(d, this.map.getNumZoomLevels() - this.map.minLogicalScale);
if (d === e) {
return
}
var c = a.xy;
if (!this.centerOnWheelEvent) {
var b = this.map.getSize();
c = new OpenLayers.Pixel(b.w / 2, b.h / 2)
}
this.map.zoomTo(d, c)
},
CLASS_NAME: 'GCUI.Control.Navigation'
});
GCUI.Control.GraphicScale = OpenLayers.Class(OpenLayers.Control, {
METRICSUIMETER: [
1000,
1,
0.01,
0.001
],
METRICSUISYMBOL: [
'km',
'm',
'cm',
'mm'
],
barAreaWidth: 150,
initialize: function (a) {
OpenLayers.Control.prototype.initialize.apply(this, [
a
]);
this.isOnTheMap = this.div ? false : true;
this.initialx = this.posx;
this.initialy = this.posy
},
updatePosition: function () {
if (this.isOnTheMap) {
if (this.initialx < 0) {
this.posx = this.map.size.w + this.initialx - parseInt(this.scaleDiv.clientWidth, 10) - 1
}
if (this.initialy < 0) {
this.posy = this.map.size.h + this.initialy - parseInt(this.scaleDiv.clientHeight, 10) - 1
}
this.div.style.left = this.posx + 'px';
this.div.style.top = this.posy + 'px'
}
},
draw: function () {
OpenLayers.Control.prototype.draw.apply(this, [
]);
this.scaleDiv = document.createElement('div');
OpenLayers.Element.addClass(this.scaleDiv, 'mapScale');
this.div.appendChild(this.scaleDiv);
this.divLegend = document.createElement('div');
this.divLegend.className = 'mapScaleLegend';
this.scaleDiv.appendChild(this.divLegend);
this.divText1 = document.createElement('div');
this.divText1.className = 'mapScaleText1';
this.scaleDiv.appendChild(this.divText1);
this.divText2 = document.createElement('div');
this.divText2.className = 'mapScaleText2';
this.scaleDiv.appendChild(this.divText2);
this.text1 = document.createTextNode('0');
this.divText1.appendChild(this.text1);
this.text2 = document.createTextNode('0');
this.divText2.appendChild(this.text2);
this.map.events.register('zoomend', this, this.update);
this.update();
return this.div
},
computeBarMetrics: function () {
var e = false;
var f,
g,
a;
var c = this.map.getResolution();
for (var d = 0; d < 4 && !e;
d++) {
f = 100000;
g = this.METRICSUIMETER[d];
while ((Math.round(f) > 0) && !e) {
a = (f * g) / c;
if (a < this.barAreaWidth) {
e = true;
this.unitInfo = this.METRICSUIMETER[d];
this.uiSymbol = this.METRICSUISYMBOL[d];
this.width = a;
this.distance = f
} else {
f /= 10
}
}
}
var b = 1;
while (b < 5) {
if ((this.width * (b + 1)) < this.barAreaWidth) {
b++
} else {
break
}
}
this.divisionCount = b;
this.width *= b;
this.distance *= b;
this.divText2.innerHTML = this.distance + ' ' + this.uiSymbol;
this.divText2.style.left = this.width + 'px'
},
update: function () {
if (this.map && this.map.baseLayer) {
this.computeBarMetrics();
var b;
var a = this.divLegend.childNodes.length;
if (this.divLegend) {
for (b = 0; b < a; b++) {
this.divLegend.removeChild(this.divLegend.childNodes[0])
}
}
this.drawBar()
}
},
drawBar: function () {
if (this.divLegend) {
this.scaleDiv.removeChild(this.divLegend)
}
this.divLegend = document.createElement('div');
this.divLegend.className = 'mapScaleLegend';
this.divLegend.style.width = this.width + 'px';
this.scaleDiv.style.width = (this.width + this.divText2.clientWidth) + 'px';
this.updatePosition();
this.scaleDiv.appendChild(this.divLegend);
if (this.divisionCount === 1) {
this.divisionCount = 5
}
var b,
a;
for (a = 0; a < this.divisionCount; a++) {
b = document.createElement('div');
b.style.width = this.width / this.divisionCount + 'px';
b.style.left = a * (this.width / this.divisionCount) + 'px';
b.className = ((a % 2) === 0) ? 'mapScaleFullBlock' : 'mapScaleEmptyBlock';
this.divLegend.appendChild(b)
}
},
toJSON: function () {
var a = this.initialy < 0 ? 'bottom' : 'top';
var b = this.initialx < 0 ? 'right' : 'left';
var c = this.map.mapName;
if (c.indexOf('.gcm') === - 1) {
c = c + '.gcm'
}
return '{"map":"' + c + '","valign":"' + a + '","halign":"' + b + '","hoffset":' + Math.abs(this.initialx) + ',"voffset":' + Math.abs(this.initialy) + ',"barsize":[5,150]}'
},
CLASS_NAME: 'GCUI.Control.GraphicScale'
});
var GCUI = GCUI || {
};
GCUI.Slider = OpenLayers.Class({
initialize: function (d, a, c, b, e) {
this.handle = d;
this.track = a;
this.axis = c || 'horizontal';
this.handle.className = (b ? b : '') + 'handle_' + this.axis;
this.track.className = (b ? b : '') + 'track_' + this.axis;
this.range = {
start: 0,
end: 100
};
this.value = 0;
this.maximum = this.range.end;
this.minimum = this.range.start;
this.alignX = 0;
this.alignY = 0;
this.setTrackAndHandleLength();
this.map = e;
this.eventMouseDown = OpenLayers.Function.bindAsEventListener(this.startDrag, this);
this.eventMouseUp = OpenLayers.Function.bindAsEventListener(this.endDrag, this);
this.eventMouseMove = OpenLayers.Function.bindAsEventListener(this.update, this);
this.setValue(parseFloat(this.range.start), 0);
this.handle.style.position = 'relative';
OpenLayers.Event.observe(this.handle, 'mousedown', this.eventMouseDown);
OpenLayers.Event.observe(this.track, 'mousedown', this.eventMouseDown)
},
setTrackAndHandleLength: function () {
this.trackLength = this.maximumOffset() - this.minimumOffset();
this.handleLength = this.isVertical() ? this.handle.offsetHeight : this.handle.offsetWidth
},
setMinimum: function (a) {
this.range.start = a;
this.minimum = this.range.start;
if (a > this.value) {
this.setValue(a)
}
if (a > this.range.end) {
this.setMaximum(a)
}
},
setMaximum: function (a) {
this.range.end = a;
this.maximum = this.range.end;
if (a < this.value) {
this.setValue(a)
}
if (a < this.range.start) {
this.setMinimum(a)
}
},
getValue: function () {
return parseInt(this.value, 10)
},
getNearestValue: function (a) {
if (a > this.range.end) {
return this.range.end
}
if (a < this.range.start) {
return this.range.start
}
return a
},
setValue: function (b, a) {
if (!this.active) {
this.activeHandle = this.handle;
this.activeHandleIdx = a;
this.updateStyles()
}
a = a || this.activeHandleIdx || 0;
b = this.getNearestValue(b);
this.value = b;
this.handle.style[this.isVertical() ? 'top' : 'left'] = this.translateToPx(b);
this.drawSpans();
if (!this.dragging || !this.event) {
this.updateFinished()
}
},
translateToPx: function (b) {
var a = Math.round(((this.trackLength - this.handleLength) / (this.range.end - this.range.start)) * (b - this.range.start));
if (isNaN(a)) {
a = 0
}
return a + 'px'
},
translateToValue: function (a) {
return ((a / (this.trackLength - this.handleLength) * (this.range.end - this.range.start)) + this.range.start)
},
minimumOffset: function () {
return (this.isVertical() ? this.alignY : this.alignX)
},
maximumOffset: function () {
return (this.isVertical() ? this.track.offsetHeight - this.alignY : this.track.offsetWidth - this.alignX)
},
isVertical: function () {
return (this.axis === 'vertical')
},
drawSpans: function () {
},
updateStyles: function () {
},
startDrag: function (b) {
var c,
d,
a;
if (OpenLayers.Event.isLeftClick(b)) {
if (!this.trackLength && !this.handleLength) {
this.setTrackAndHandleLength()
}
OpenLayers.Event.observe(this.track.parentNode, 'mouseup', this.eventMouseUp);
OpenLayers.Event.observe(document, 'mouseup', this.eventMouseUp);
if (this.map) {
this.map.events.on({
mouseup: this.eventMouseUp
})
}
OpenLayers.Event.observe(this.track.parentNode, 'mousemove', this.eventMouseMove);
this.active = true;
c = OpenLayers.Event.element(b);
d = [
this.pointerX(b),
this.pointerY(b)
];
if (c == this.track) {
a = this.cumulativeOffset(this.track);
this.event = b;
this.setValue(this.translateToValue((this.isVertical() ? d[1] - a[1] : d[0] - a[0]) - (this.handleLength / 2)));
a = this.cumulativeOffset(this.activeHandle);
this.offsetX = (d[0] - a[0]);
this.offsetY = (d[1] - a[1])
} else {
this.activeHandle = c;
this.activeHandleIdx = 0;
this.updateStyles();
a = this.cumulativeOffset(this.activeHandle);
this.offsetX = (d[0] - a[0]);
this.offsetY = (d[1] - a[1])
}
OpenLayers.Event.stop(b)
}
},
update: function (a) {
if (this.active) {
if (!this.dragging) {
this.dragging = true
}
this.draw(a);
if (navigator.appVersion.indexOf('AppleWebKit') > 0) {
window.scrollBy(0, 0)
}
OpenLayers.Event.stop(a)
}
},
draw: function (b) {
var c = [
this.pointerX(b),
this.pointerY(b)
];
var a = this.cumulativeOffset(this.track);
c[0] -= this.offsetX + a[0];
c[1] -= this.offsetY + a[1];
this.event = b;
this.setValue(this.translateToValue(this.isVertical() ? c[1] : c[0]))
},
endDrag: function (a) {
if (this.active && this.dragging) {
this.finishDrag(a, true);
OpenLayers.Event.stop(a)
}
this.active = false;
this.dragging = false;
if (typeof this.onrelease === 'function') {
this.onrelease()
}
OpenLayers.Event.stopObserving(this.track.parentNode, 'mouseup', this.eventMouseUp);
OpenLayers.Event.stopObserving(document, 'mouseup', this.eventMouseUp);
if (this.map) {
this.map.events.un({
mouseup: this.eventMouseUp
})
}
OpenLayers.Event.stopObserving(this.track.parentNode, 'mousemove', this.eventMouseMove)
},
finishDrag: function (a, b) {
this.active = false;
this.dragging = false;
this.updateFinished()
},
updateFinished: function () {
if (this.onChange) {
this.onChange(this.getValue(), this)
}
this.event = null
},
cumulativeOffset: function (b) {
var a = 0,
c = 0;
if (/Konqueror|Safari|KHTML/.test(navigator.userAgent)) {
do {
a += b.offsetTop || 0;
c += b.offsetLeft || 0;
if (b.offsetParent == document.body && b.style.position === 'absolute') {
break
}
b = b.offsetParent
} while (b)
} else {
do {
a += b.offsetTop || 0;
c += b.offsetLeft || 0;
b = b.offsetParent
} while (b)
}
return [c,
a]
},
pointerX: function (c) {
var b = document.documentElement,
a = document.body || {
scrollLeft: 0
};
return c.pageX || (c.clientX + (b.scrollLeft || a.scrollLeft) - (b.clientLeft || 0))
},
pointerY: function (c) {
var b = document.documentElement,
a = document.body || {
scrollTop: 0
};
return c.pageY || (c.clientY + (b.scrollTop || a.scrollTop) - (b.clientTop || 0))
}
});
GCUI = GCUI || {
};
GCUI.Control = GCUI.Control || OpenLayers.Control;
GCUI.Control.ScaleSlider = OpenLayers.Class(OpenLayers.Control, {
autoActivate: true,
initialize: function (a) {
OpenLayers.Control.prototype.initialize.apply(this, [
a
]);
this.inverseDir = this.inverseDir || false;
this.orientation = this.orientation || 'vertical';
this.isOnTheMap = true
},
activate: function () {
this.slider = new GCUI.Slider(this.sliderHandle, this.sliderTrack, this.orientation, '', this.map);
this.setMinimumScale(this.map.minLogicalScale);
this.setMaximumScale(this.map.maxLogicalScale);
this.setScale(this.map.getNumZoomLevels() - this.map.zoom);
this.slider.onrelease = OpenLayers.Function.bindAsEventListener(this.onrelease, this);
return OpenLayers.Control.prototype.activate.apply(this, [
])
},
destroy: function () {
this.map.events.un({
zoomend: this.setScale,
scope: this
});
this.slider.onrelease = null;
OpenLayers.Control.prototype.destroy.apply(this, [
])
},
draw: function (a) {
OpenLayers.Control.prototype.draw.apply(this, [
a
]);
this.div.className = 'mapslider';
this.div.id = this.map.div.id + '_slider';
var b = document.createElement('div');
this.div.appendChild(b);
var c = document.createElement('div');
b.appendChild(c);
this.createZoomButton('mapsliderZoomIn', - 1);
this.createZoomButton('mapsliderZoomOut', 1);
this.sliderTrack = b;
this.sliderHandle = c;
this.map.events.register('zoomend', this, this.setScale);
return this.div
},
createZoomButton: function (a, c) {
var b = document.createElement('div');
b.className = a;
b.onclick = OpenLayers.Function.bindAsEventListener(this.zoom, {
slider: this,
delta: c
});
this.div.appendChild(b)
},
zoom: function (a) {
this.slider.map.animateZoom(this.slider.map.getNumZoomLevels() - this.slider.map.getZoom() + this.delta);
OpenLayers.Event.stop(a)
},
onrelease: function () {
this.map.animateZoom(this.map.getNumZoomLevels() - this.getScale())
},
setScale: function (a) {
if (a.object) {
a = a.object.getNumZoomLevels() - a.object.zoom
}
if (!this.slider) {
return
}
this.slider.setValue(this.transformValue(a))
},
getScale: function () {
return this.inverseDir ? this.slider.getValue()  : this.map.getNumZoomLevels() - this.slider.getValue()
},
transformValue: function (a) {
if (this.inverseDir) {
return (this.maximum - a) + this.minimum
} else {
return a
}
},
setMinimumScale: function (a) {
this.minimum = a;
if (this.slider) {
this.slider.setMinimum(a)
}
},
setMaximumScale: function (a) {
this.maximum = a;
if (this.slider) {
this.slider.setMaximum(a)
}
},
CLASS_NAME: 'GCUI.Control.ScaleSlider'
});
GCUI.Control.LayerSwitcher = OpenLayers.Class(OpenLayers.Control, {
layerStates: null,
layersDiv: null,
minimizeDiv: null,
maximizeDiv: null,
ascending: false,
initialize: function (a) {
OpenLayers.Control.prototype.initialize.apply(this, [
a
]);
this.layerStates = [
]
},
destroy: function () {
OpenLayers.Event.stopObservingElement(this.div);
this.clear();
this.map.events.un({
addlayer: this.redraw,
changelayer: this.redraw,
removelayer: this.redraw,
changebaselayer: this.redraw,
scope: this
});
OpenLayers.Control.prototype.destroy.apply(this, [
])
},
clear: function () {
this.clearLayersArray('data');
for (var a in this.groups) {
if (this.groups.hasOwnProperty(a)) {
if (this[a + 'Div']) {
OpenLayers.Event.stopObservingElement(this[a + 'Div'])
}
if (this[a + 'LayersDiv']) {
this[a + 'LayersDiv'].innerHTML = '';
delete this[a + 'LayersDiv']
}
}
}
this.layersDiv.innerHTML = ''
},
setMap: function (a) {
OpenLayers.Control.prototype.setMap.apply(this, [
a
]);
this.map.events.on({
addlayer: this.redraw,
changelayer: this.redraw,
removelayer: this.redraw,
changebaselayer: this.redraw,
scope: this
})
},
draw: function () {
OpenLayers.Control.prototype.draw.apply(this);
this.div.className = 'mapLayerControl';
this.loadContents();
this.redraw();
return this.div
},
clearLayersArray: function (d) {
var e = this[d + 'Layers'];
if (e) {
for (var c = 0, a = e.length; c < a; c++) {
var b = e[c];
OpenLayers.Event.stopObservingElement(b.inputElem);
OpenLayers.Event.stopObservingElement(b.labelSpan);
OpenLayers.Event.stopObservingElement(b.upSpan);
OpenLayers.Event.stopObservingElement(b.downSpan)
}
}
this[d + 'Layers'] = [
]
},
checkRedraw: function () {
var e = false;
if (!this.layerStates.length || (this.map.layers.length != this.layerStates.length)) {
e = true
} else {
for (var c = 0, a = this.layerStates.length; c < a; c++) {
var d = this.layerStates[c];
var b = this.map.layers[c];
if ((d.name != b.name) || (d.inRange != b.inRange) || (d.id != b.id) || (d.visibility != b.visibility)) {
e = true;
break
}
}
}
return e
},
redraw: function (b) {
if (b && b.property == 'opacity' && b.layer.slider) {
b.layer.slider.setValue(b.layer.opacity * 100)
}
if (
/*!(options && options.json) &&*/
!this.checkRedraw()) {
return this.div
}
this.clear();
if (b && b.json) {
return
}
var a = this.map.layers.length;
this.layerStates = [
];
for (var e = 0; e < a; e++) {
var d = this.map.layers[e];
this.layerStates[e] = {
name: d.name,
visibility: d.visibility,
inRange: d.inRange,
id: d.id
}
}
var f = this.map.layers.slice();
if (!this.ascending) {
f.reverse()
}
for (e = 0; e < a; e++) {
var d = f[e];
if (d.displayInLayerSwitcher) {
this.createLayerDiv(d)
}
}
if (this.groups) {
for (var c in this.groups) {
if (!this.groups[c].expanded && this[c + 'Div']) {
this.groupClick(c, this[c + 'Div'].firstChild)
}
}
}
return this.div
},
createOpacitySlider: function (a, b) {
var f = document.createElement('div');
f.className = 'mapLayerSlider';
var e = document.createElement('div');
e.id = 'divTrack' + b.id;
f.appendChild(e);
var d = document.createElement('div');
d.id = 'divHandle' + b.id;
e.appendChild(d);
if (b.getVisibility()) {
a.appendChild(f)
}
var c = new GCUI.Slider(d, e, 'horizontal', 'layer', b.map);
c.setValue(b.opacity * 100);
f.title = c.getValue() + '%';
c.drawSpans = OpenLayers.Function.bind(this.updateOpacityControl, this, c, b, f);
b.slider = c
},
updateOpacityControl: function (b, a, c) {
c.title = b.getValue() + '%';
a.setOpacity(b.getValue() / 100)
},
onUpClick: function (c) {
var b = this.layer.map;
var a = b.getLayerIndex(this.layer);
var d = 1;
if (!this.layer.group && b.layers[a + 1] && b.layers[a + 1].group) {
d = this.switcher.groups[b.layers[a + 1].group].size
}
b.setLayerIndex(this.layer, a + d);
OpenLayers.Event.stop(c)
},
onDownClick: function (c) {
var b = this.layer.map;
var a = b.getLayerIndex(this.layer);
var d = 1;
if (!this.layer.group && b.layers[a - 1] && b.layers[a - 1].group) {
d = this.switcher.groups[b.layers[a - 1].group].size
}
b.setLayerIndex(this.layer, a - d);
OpenLayers.Event.stop(c)
},
onInputClick: function (b) {
var a = this.layer.visibility;
this.inputElem.className = (a ? 'mapLayerUnCheckbox' : 'mapLayerCheckbox') + (this.layer.group ? '' : ' mapLayerLeft');
this.layer.setVisibility(!a);
OpenLayers.Event.stop(b)
},
maximizeControl: function (a) {
this.div.style.width = '';
this.div.style.height = '';
this.showControls(false);
this.ignoreEvent(a)
},
minimizeControl: function (a) {
this.div.style.width = '0px';
this.div.style.height = '0px';
this.showControls(true);
this.ignoreEvent(a)
},
showControls: function (a) {
this.layersDiv.style.display = a ? 'none' : ''
},
loadContents: function () {
OpenLayers.Event.observe(this.div, 'mouseup', OpenLayers.Function.bindAsEventListener(this.mouseUp, this));
OpenLayers.Event.observe(this.div, 'click', this.ignoreEvent);
OpenLayers.Event.observe(this.div, 'mousedown', OpenLayers.Function.bindAsEventListener(this.mouseDown, this));
OpenLayers.Event.observe(this.div, 'dblclick', this.ignoreEvent);
this.layersDiv = document.createElement('div');
this.layersDiv.id = this.id + '_layersDiv';
OpenLayers.Element.addClass(this.layersDiv, 'layersDiv');
this.div.appendChild(this.layersDiv)
},
ignoreEvent: function (a) {
if (a != null) {
OpenLayers.Event.stop(a)
}
},
mouseDown: function (a) {
this.isMouseDown = true;
this.ignoreEvent(a)
},
mouseUp: function (a) {
if (this.isMouseDown) {
this.isMouseDown = false;
this.ignoreEvent(a)
}
},
groupClick: function (d, a) {
var c = this[d + 'LayersDiv'];
var b = (c.style.display == 'block');
c.style.display = b ? 'none' : 'block';
a.className = 'mapGroup' + (b ? 'Plus' : 'Minus');
this.groups[d].expanded = !b
},
createGroup: function (e) {
if (!this[e + 'LayersDiv']) {
var c = document.createElement('div');
c.className = 'mapGroupInfo';
var b = document.createElement('div');
b.className = 'mapGroupMinus';
c.appendChild(b);
var a = document.createElement('div');
a.innerHTML = e;
a.className = 'mapGroupName';
c.appendChild(a);
var d = document.createElement('div');
d.style.display = 'block';
this.layersDiv.appendChild(c);
this.layersDiv.appendChild(d);
this[e + 'LayersDiv'] = d;
OpenLayers.Event.observe(c, 'click', OpenLayers.Function.bind(this.groupClick, this, e, b));
this[e + 'Div'] = c
}
return this[e + 'LayersDiv']
},
createLayerDiv: function (g) {
var l = document.createElement('div');
l.className = 'mapLayerInfo';
if (g.group) {
var h = document.createElement('div');
h.className = 'mapLayerNode';
l.appendChild(h)
}
var i = document.createElement('div');
i.className = (g.getVisibility() ? 'mapLayerCheckbox' : 'mapLayerUncheckbox') + (g.group ? '' : ' mapLayerLeft');
l.appendChild(i);
var a = {
inputElem: i,
layer: g,
switcher: this
};
OpenLayers.Event.observe(i, 'mouseup', OpenLayers.Function.bindAsEventListener(this.onInputClick, a));
var b = document.createElement('div');
OpenLayers.Element.addClass(b, 'mapLayerName' + (g.depth ? '' : ' mapLayerLeft'));
var f = g.label || ((g.name == 'main') ? (g.layer || g.tabname)  : g.name);
if (f.length >= GCUI.Control.LayerSwitcher.LAYER_LABEL_MAXLENGTH) {
b.title = f;
f = f.substring(0, GCUI.Control.LayerSwitcher.LAYER_LABEL_REPLACEMENT_INDEX) + GCUI.Control.LayerSwitcher.LAYER_LABEL_SUFFIX_REPLACEMENT
}
b.innerHTML = f;
OpenLayers.Event.observe(b, 'click', OpenLayers.Function.bindAsEventListener(this.onInputClick, a));
l.appendChild(b);
var j = g.group ? this.map.getLayersBy('group', g.group)  : this.map.getLayersBy('displayInLayerSwitcher', true);
var e = OpenLayers.Util.indexOf(j, g);
var c = document.createElement('div');
c.className = 'mapLayerUp';
l.appendChild(c);
OpenLayers.Event.observe(c, 'click', OpenLayers.Function.bindAsEventListener(this.onUpClick, a));
c.style.display = (e == j.length - 1) ? 'none' : 'block';
var d = document.createElement('div');
d.className = 'mapLayerDown';
l.appendChild(d);
OpenLayers.Event.observe(d, 'click', OpenLayers.Function.bindAsEventListener(this.onDownClick, a));
d.style.display = (e == 0) ? 'none' : 'block';
this.dataLayers.push({
inputElem: i,
labelSpan: b,
upSpan: c,
downSpan: d
});
var k = g.group ? this.createGroup(g.group)  : this.layersDiv;
k.appendChild(l);
this.createOpacitySlider(l, g)
},
initFromJson: function (k) {
var l = this.map.baseLayer.url;
var c = this.map.baseLayer.mapname;
this.map.baseLayer.setVisibility(false);
this.map.baseLayer.displayInLayerSwitcher = false;
for (var f = 0; f < this.map.layers.length; f++) {
var g = this.map.layers[f];
if (g.displayInLayerSwitcher && this.map.baseLayer != g) {
this.map.removeLayer(g)
}
}
var e = k.layers;
var j = null;
this.groups = [
];
for (var f = 0, h = e.length; f < h; f++) {
var g = e[f];
if (g.depth === 0) {
if (g.expanded !== undefined) {
j = g.label || g.name;
this.groups[j] = {
expanded: g.expanded,
size: 0
}
}
} else {
g.group = j;
this.groups[j].size++
}
}
e = e.reverse();
for (var f = 0, h = e.length; f < h; f++) {
var g = e[f];
var d;
if (g.depth || (g.depth === 0 && g.expanded === undefined)) {
var b = g.server || l;
if (g.type === 'vector') {
d = new OpenLayers.Layer.Vector(g.name, {
opacity: g.opacity / 100,
visibility: g.visibility,
strategies: [
  new OpenLayers.Strategy.BBOX({
    resFactor: 1,
    ratio: 1
  })
],
protocol: new OpenLayers.Protocol.Script({
  url: b.replace('/maps', '') + '/api/lbs/layer/geojson',
  params: {
    name: g.name
  },
  createRequest: function (n, p, r) {
    var q = OpenLayers.Protocol.Script.register(r);
    var m = OpenLayers.String.format(this.callbackTemplate, {
      id: q
    });
    p = OpenLayers.Util.extend({
    }, p);
    p[this.callbackKey] = m + '&&' + m;
    n = OpenLayers.Util.urlAppend(n, OpenLayers.Util.getParameterString(p));
    var i = document.createElement('script');
    i.type = 'text/javascript';
    i.src = n;
    i.id = 'OpenLayers_Protocol_Script_' + q;
    this.pendingRequests[i.id] = i;
    var o = document.getElementsByTagName('head') [0];
    o.appendChild(i);
    return i
  },
  format: new OpenLayers.Format.GeoJSON(),
  handleRead: function (i, m) {
    i.data = i.data.result;
    this.handleResponse(i, m)
  }
})
})
} else {
var a = g.mapname || c;
d = new GCUI.Layer.GeoConcept(g.name, b, {
mapname: a,
tabname: g.internalName,
layer: g.name,
extension: g.format,
singleTile: g.singleTiledLayer
}, {
isBaseLayer: false,
visibility: g.visibility,
group: g.group,
depth: g.depth,
opacity: g.opacity / 100,
label: g.label,
transparent: g.isTransparent || g.transparent
})
}
this.map.addLayer(d)
}
}
},
getLayersJson: function (c) {
var b = this.map.baseLayer.url;
var a = b.lastIndexOf('/map') !== - 1 ? b.lastIndexOf('/map')  : b.lastIndexOf('/wmts');
var d = b.substring(0, a);
var e = new OpenLayers.Protocol.Script({
url: d + '/HtcLayer/showJson.do',
params: {
type: 'layers',
name: c,
T: new Date().getTime()
},
format: new OpenLayers.Format(),
handleResponse: function (g, f) {
this.destroyRequest(g.priv);
if (g.data) {
f.callback.call(f.scope, g.data)
} else {
GCUI.Console.error('LayerSwitcher. No JSON for ' + c)
}
},
callback: function (f) {
this.initFromJson(f)
},
scope: this
});
e.read()
},
CLASS_NAME: 'GCUI.Control.LayerSwitcher'
});
GCUI.Control.LayerSwitcher.LAYER_LABEL_MAXLENGTH = 18;
GCUI.Control.LayerSwitcher.LAYER_LABEL_REPLACEMENT_INDEX = 14;
GCUI.Control.LayerSwitcher.LAYER_LABEL_SUFFIX_REPLACEMENT = '...';
GCUI.Control.Popup = OpenLayers.Class(OpenLayers.Control, {
positionBlocks: {
tl: {
left: true,
top: true
},
tr: {
left: false,
top: true
},
bl: {
left: true,
top: false
},
br: {
left: false,
top: false
}
},
initialize: function (b, a, d, c) {
this.id = OpenLayers.Util.createUniqueID(this.CLASS_NAME + '_');
this.lonlat = b;
this.contentHTML = a
},
destroy: function () {
this.map.events.un({
move: this.updatePosition,
scope: this
});
OpenLayers.Control.prototype.destroy.apply(this, [
])
},
draw: function (a) {
this.div = document.createElement('div');
this.div.style.width = '';
this.div.style.height = '';
this.div.style.visibility = 'visible';
this.div.className = 'mapsheet';
this.events = new OpenLayers.Events(this, this.div, null, true);
this.events.on({
click: this.onclick,
mousedown: this.onmousedown,
mousemove: this.onmousemove,
mouseup: this.onmouseup,
mouseout: this.onmouseout,
touchstart: this.onTouchstart,
scope: this
});
if (a == null && (this.lonlat != null) && (this.map != null)) {
a = this.map.getViewPortPxFromLayerPx(this.map.getLayerPxFromLonLat(this.lonlat))
}
this.moveTo(a);
this.setContentHTML();
this.relativePosition = this.calculateRelativePosition(a);
this.isUpdateRelativePosition = false;
this.map.events.register('move', this, this.updatePosition);
return this.div
},
calculateRelativePosition: function (b) {
var d = this.map.getLonLatFromLayerPx(b);
var c = this.map.getExtent();
var a = c.determineQuadrant(d);
return OpenLayers.Bounds.oppositeQuadrant(a)
},
onclick: function (a) {
var b = a.target || a.srcElement;
if (b.tagName != 'IMG' && b.tagName != 'A') {
this.destroy()
}
OpenLayers.Event.stop(a, true)
},
updateRelativePosition: function () {
this.isUpdateRelativePosition = true;
if (this.relativePosition) {
var b = this.positionBlocks[this.relativePosition];
var d = b.left;
var f = b.top;
var c = this.div.offsetWidth;
var g = this.div.offsetHeight;
if (this.div.style.width == '') {
this.div.style.width = c + 'px'
}
var i = parseInt(this.div.style.left);
var a = parseInt(this.div.style.top);
var e = new OpenLayers.Pixel(d ? (i - c)  : i, f ? (a - g)  : a);
this.moveTo(e)
}
},
updatePosition: function () {
if ((this.lonlat) && (this.map)) {
var a = this.map.getViewPortPxFromLayerPx(this.map.getLayerPxFromLonLat(this.lonlat));
if (a) {
this.moveTo(a);
if (this.contentHTML != '' && this.isUpdateRelativePosition) {
this.updateRelativePosition(a)
}
}
}
},
setContentHTML: function (a) {
if (a != null) {
this.contentHTML = a
}
if ((this.div != null) && (this.contentHTML != null) && (this.contentHTML != this.div.innerHTML)) {
this.div.innerHTML = '<div>' + this.contentHTML + '</div>'
}
},
onmousedown: function (a) {
this.mousedown = true;
OpenLayers.Event.stop(a, true)
},
onmousemove: function (a) {
if (this.mousedown) {
OpenLayers.Event.stop(a, true)
}
},
onmouseup: function (a) {
if (this.mousedown) {
this.mousedown = false;
OpenLayers.Event.stop(a, true)
}
},
onmouseout: function (a) {
this.mousedown = false
},
onTouchstart: function (a) {
OpenLayers.Event.stop(a, true)
},
CLASS_NAME: 'GCUI.Control.Popup'
});
GCUI.Control.GlobalView = OpenLayers.Class(OpenLayers.Control, {
initialize: function (a) {
OpenLayers.Control.prototype.initialize.apply(this, [
a
]);
this.initialx = this.posx;
this.initialy = this.posy;
this.initialw = this.width;
this.initialh = this.height;
this.visible = true;
if (this.format !== 'png' && this.format !== 'jpg' && this.format !== 'png24') {
this.format = 'png'
}
this.zooms = [
];
this.scales = [
];
this.crossWidth = 10;
this.crossHeight = 10;
this.crossLineWidth = 2;
this.defaultImg = OpenLayers.Util.getImagesLocation() + 'blank.gif';
this.handlers = {
}
},
draw: function () {
OpenLayers.Control.prototype.draw.apply(this, [
]);
this.map.onEvent('load', OpenLayers.Function.bind(this.create, this));
return this.div
},
updateCrossDiv: function () {
this.crossdivH.style.width = this.crossWidth + 'px';
this.crossdivH.style.height = this.crossLineWidth + 'px';
this.crossdivV.style.width = this.crossLineWidth + 'px';
this.crossdivV.style.height = this.crossHeight + 'px'
},
create: function () {
this.server = this.map.server;
this.minscale = this.map.minLogicalScale;
this.maxscale = this.map.maxLogicalScale;
this.mapName = this.mapName || this.map.baseLayer.getMapName();
this.tabName = this.tabName || this.map.baseLayer.tabname;
var f;
for (f = 0; f < this.maxscale; f++) {
this.zooms[f] = 1;
this.scales[f] = this.maxscale
}
if (this.tabgrad) {
this.zooms = this.tabgrad;
this.zoomsFixed = true
}
if (this.tabscales) {
this.scales = this.tabscales
}
this.autoZoomReduction = true;
this.sizeProportion = 60 / 100;
this.margins = [
8,
- 2,
- 1,
7
];
if (this.isFixed) {
this.sizeProportion = 1;
var d = this.map.precision;
var k = this.map.baseLayer.maxExtent;
var b = [
k.left / d,
k.right / d,
k.bottom / d,
k.top / d
];
this.initCenterX = b[0] + (b[1] - b[0]) / 2;
this.initCenterY = b[2] + (b[3] - b[2]) / 2;
this.autoZoomReduction = false
}
if (!this.outsideViewport) {
OpenLayers.Element.addClass(this.div, 'mapglobalview');
this.div.style.width = this.width + 'px';
this.div.style.height = this.height + 'px';
this.calcGlobalViewPositions()
}
this.minidiv = document.createElement('div');
this.minidiv.innerHTML = '';
this.tileWidth = this.map.baseLayer.tileSize.w;
this.tileHeight = this.map.baseLayer.tileSize.h;
this.marginX = 200;
this.marginY = 200;
var h = this.map.size.w + 2 * this.marginX;
var j = this.map.size.h + 2 * this.marginY;
var g = Math.floor(h / this.tileWidth) + 2;
var e = Math.floor(j / this.tileHeight) + 2;
var c;
var a;
this.images = [
];
this.animImages = [
];
this.nbTileX = g;
this.nbTileY = e;
for (c = 0; c < this.nbTileX; c++) {
this.images[c] = [
];
this.animImages[c] = [
];
for (a = 0; a < this.nbTileY; a++) {
this.images[c][a] = this.createImage(this.minidiv, 0)
}
}
this.minidiv.className = 'minidiv';
this.minidiv.style.position = 'absolute';
this.minidiv.style.width = this.width + 'px';
this.minidiv.style.height = this.height + 'px';
this.divEvents = new OpenLayers.Events(this, this.minidiv, null, false);
this.divEvents.on({
mousemove: OpenLayers.Event.stop
});
this.div.appendChild(this.minidiv);
this.pandiv = document.createElement('div');
this.pandiv.className = 'pandiv';
this.pandiv.style.position = 'absolute';
this.crossdivH = document.createElement('div');
this.crossdivH.className = 'crossdivHorizontal';
this.crossdivH.style.position = 'absolute';
this.crossdivV = document.createElement('div');
this.crossdivV.className = 'crossdivVertical';
this.crossdivV.style.position = 'absolute';
this.updateCrossDiv();
this.minidiv.appendChild(this.pandiv);
this.minidiv.appendChild(this.crossdivH);
this.minidiv.appendChild(this.crossdivV);
this.divIco = document.createElement('div');
this.divIco.className = 'mapglobalviewico';
this.div.appendChild(this.divIco);
this.divIco.onclick = OpenLayers.Function.bind(this.animateView, this);
this.map.events.register('moveend', this, this.refresh);
this.handlers.drag = new OpenLayers.Handler.Drag(this, {
down: this.pan,
move: this.panMove,
done: this.panUp
}, {
});
this.rectEvents = new OpenLayers.Events(this, this.pandiv, null, false);
this.eventPan = OpenLayers.Function.bindAsEventListener(this.pan, this);
this.eventPanMove = OpenLayers.Function.bindAsEventListener(this.panMove, this);
this.eventPanUp = OpenLayers.Function.bindAsEventListener(this.panUp, this);
this.rectEvents.register('mouseover', this, function (i) {
if (!this.handlers.drag.active && !this.map.dragging) {
this.handlers.drag.activate();
this.divEvents.un({
mousemove: OpenLayers.Event.stop
})
}
if (this.outsideViewport) {
OpenLayers.Event.observe(document, 'mousedown', this.eventPan)
}
});
this.rectEvents.register('mouseout', this, function (i) {
if (!this.handlers.drag.dragging) {
this.handlers.drag.deactivate();
this.divEvents.on({
mousemove: OpenLayers.Event.stop
})
}
if (this.outsideViewport) {
OpenLayers.Event.stopObserving(document, 'mousedown', this.eventPan)
}
});
this.divEvents.on({
mouseover: function () {
this.divEvents.on({
click: this.centerMap
})
},
scope: this
});
this.divEvents.on({
mouseout: function () {
this.divEvents.un({
click: this.centerMap
})
},
scope: this
});
this.moveStart();
this.refresh(true);
this.created = true
},
getLogicalScale: function () {
return this.map.getNumZoomLevels() - this.map.zoom
},
createImage: function (f, e) {
var d = document.createElement('img');
var a = this.getLogicalScale();
var c = this.zooms[a - 1];
d.style.width = Math.round(this.tileWidth * c + 0.5) + 'px';
d.style.height = Math.round(this.tileHeight * c + 0.5) + 'px';
d.style.display = 'none';
d.style.position = 'absolute';
d.style.left = '-500px';
d.style.zIndex = e;
d.style.border = '0';
d.galleryImg = false;
d.src = this.defaultImg;
f.appendChild(d);
var b = {
nosrc: true,
image: d,
visible: false
};
return b
},
calcMapTileWidth: function () {
var a = this.map.getNumZoomLevels() - this.scales[this.getLogicalScale() - 1];
var b = this.map.precision / this.map.getResolutionForZoom(a);
return this.tileWidth / b
},
calcMapTileHeight: function () {
var a = this.map.getNumZoomLevels() - this.scales[this.getLogicalScale() - 1];
var b = this.map.precision / this.map.getResolutionForZoom(a);
return this.tileHeight / b
},
calcNumTileX: function (a) {
return Math.floor(a / this.calcMapTileWidth())
},
calcNumTileY: function (a) {
return Math.floor(a / this.calcMapTileHeight())
},
calcMapDeltaX: function (c, b) {
var a = this.map.getNumZoomLevels() - this.scales[this.getLogicalScale() - 1];
var d = this.map.precision / this.map.getResolutionForZoom(a);
return c + b / d
},
calcMapDeltaY: function (c, b) {
var a = this.map.getNumZoomLevels() - this.scales[this.getLogicalScale() - 1];
var d = - this.map.precision / this.map.getResolutionForZoom(a);
return c + b / d
},
panMove: function (s) {
this.panning = true;
var m = this.pandiv;
var l = this.getXposition(s);
var o = this.getYposition(s);
var j = l - m.tmpX;
var i = o - m.tmpY;
var k = OpenLayers.Util.pagePosition(this.div) [0];
var t = OpenLayers.Util.pagePosition(this.div) [1];
m.style.top = (parseInt(m.style.top, 10) + i) + 'px';
m.style.left = (parseInt(m.style.left, 10) + j) + 'px';
m.tmpX = l;
m.tmpY = o;
var a = OpenLayers.Util.pagePosition(m) [0];
var c = OpenLayers.Util.pagePosition(m) [1];
var e = k + parseInt(this.width, 10) - a + j - parseInt(m.style.width, 10);
var b = a + parseInt(m.style.left, 10) + j - k + parseInt(this.minidiv.style.left, 10);
var h = t + parseInt(this.height, 10) - c - parseInt(m.style.height, 10);
var n = c + parseInt(m.style.top, 10) - t + parseInt(this.minidiv.style.top, 10);
var f = this.margins[0];
var r = this.margins[1];
var d = this.margins[2];
var g = this.margins[3];
if (!this.isFixed) {
if (e < f) {
m.style.left = (parseInt(m.style.left, 10) - j - 1) + 'px';
m.diffX = m.diffX - j - 1
}
if (b < r) {
m.style.left = (parseInt(m.style.left, 10) - j + 1) + 'px';
m.diffX = m.diffX - j + 1
}
if (n < d) {
m.style.top = (parseInt(m.style.top, 10) - i + 1) + 'px';
m.diffY = m.diffY - i + 1
}
if (h < g) {
m.style.top = (parseInt(m.style.top, 10) - i - 1) + 'px';
m.diffY = m.diffY - i - 1
}
}
if ((e < f) || (b < r) || (h < g) || (n < d)) {
if (!this.miniscrolling && !this.isFixed) {
this.miniscrolling = true
}
} else {
this.miniscrolling = false;
var q = this.map.getCenter().lon / this.map.precision;
var p = this.map.getCenter().lat / this.map.precision;
m.beginDragX = q;
m.beginDragY = p
}
if (this.outsideViewport) {
OpenLayers.Event.observe(document, 'mouseup', this.eventPanUp)
}
},
panUp: function (a) {
this.tempCurrentX = null;
this.tempCurrentY = null;
var d = this.pandiv;
var j = d.tmpX - d.beginDragCursorX + d.diffX;
var i = d.tmpY - d.beginDragCursorY + d.diffY;
var c = this.map.getCenter().lon / this.map.precision;
var b = this.map.getCenter().lat / this.map.precision;
d.beginDragX = c;
d.beginDragY = b;
this.panning = false;
var g = this.getLogicalScale();
var h = this.zooms[g - 1];
var e = this.calcMapDeltaX(d.beginDragX, j / h) * this.map.precision;
var f = this.calcMapDeltaY(d.beginDragY, i / h) * this.map.precision;
this.map.setCenter(new OpenLayers.LonLat(e, f));
if (this.outsideViewport) {
OpenLayers.Event.stopObserving(document, 'mouseup', this.eventPanUp);
OpenLayers.Event.stopObserving(document, 'mousemove', this.eventPanMove)
}
},
pan: function (c) {
if (c.preventDefault) {
c.preventDefault()
}
var d = this.pandiv;
d.beginDragCursorX = this.getXposition(c);
d.beginDragCursorY = this.getYposition(c);
var b = this.map.getCenter().lon / this.map.precision;
var a = this.map.getCenter().lat / this.map.precision;
d.beginDragX = b;
d.beginDragY = a;
d.tmpX = d.beginDragCursorX;
d.tmpY = d.beginDragCursorY;
d.diffX = 0;
d.diffY = 0;
if (this.outsideViewport) {
OpenLayers.Event.observe(document, 'mousemove', this.eventPanMove)
}
},
moveStart: function () {
this.centerStartPx = this.map.getViewPortPxFromLonLat(this.map.getCenter())
},
move: function (i, h, f) {
if (this.isFixed) {
this.positionRect()
} else {
if (!this.firstRefreshDone) {
this.refresh()
} else {
var e = this.map.getViewPortPxFromLayerPx(this.map.getViewPortPxFromLonLat(this.map.getCenter()));
i = e.x - this.centerStartPx.x;
h = e.y - this.centerStartPx.y;
if (Math.abs(i) > this.map.size.w / 2 || Math.abs(h) > this.map.size.h / 2) {
this.refresh()
} else {
var a = this.pandiv.style;
var d = this.minidiv.style;
var g = this.zooms[this.getLogicalScale() - 1];
var b = this.map.getResolution() / this.map.precision;
var c = b / (this.calcMapDeltaX(0, 1 / g));
d.top = Math.round((this.height - parseInt(d.height, 10)) / 2) + c * h + 'px';
d.left = Math.round((this.width - parseInt(d.width, 10)) / 2) + c * i + 'px';
a.top = Math.round(((this.height - parseInt(a.height, 10)) / 2 - parseInt(d.top, 10))) + 'px';
a.left = Math.round(((this.width - parseInt(a.width, 10)) / 2 - parseInt(d.left, 10))) + 'px';
this.positionCross()
}
}
}
},
calcLimits: function () {
var g = this.map.precision;
var d = this.map.baseLayer.maxExtent;
var a = [
d.left / g,
d.right / g,
d.bottom / g,
d.top / g
];
var c = this.calcNumTileX(a[0]);
var b = this.calcNumTileX(a[1]);
var f = this.calcNumTileY(a[2]);
var e = this.calcNumTileY(a[3]);
this.tileLimits = [
c,
b + 1,
f,
e + 1
]
},
isVisible: function () {
return this.visible
},
calcTileSrc: function (b) {
var c = this.format;
var a = this.getLogicalScale();
var e = this.scales[a - 1];
var d = [
this.server,
escape(this.mapName),
0,
escape(this.tabName),
c,
this.tileWidth,
this.tileHeight,
e,
b.mapTileX,
b.mapTileY + '.' + c.substring(0, 3)
];
return d.join('/')
},
clearImage: function (c) {
c.image.src = this.defaultImg;
c.image.style.display = 'none';
var a = this.getLogicalScale();
var b = this.zooms[a - 1];
c.image.style.width = Math.round(this.tileWidth * b + 0.5) + 'px';
c.image.style.height = Math.round(this.tileHeight * b + 0.5) + 'px';
c.nosrc = true;
c.visible = false;
c.image.galleryImg = false
},
clearAll: function (a) {
if (!a) {
return
}
var d,
c,
b;
for (d = 0;
d < this.nbTileX; d++) {
for (c = 0; c < this.nbTileY; c++) {
b = a[d][c];
this.clearImage(b)
}
}
},
refresh: function (b) {
if (!this.outsideViewport && b) {
this.calcGlobalViewPositions()
}
if (!this.visible || !this.map.initialized) {
return
}
var i,
f,
j,
g;
var q,
o,
h,
e;
var r,
l,
k,
c;
if (!this.isFixed || !this.firstRefreshDone) {
this.clearAll(this.images);
this.calcLimits();
i = Math.floor(this.nbTileX / 2);
f = Math.floor(this.nbTileY / 2);
this.centerX = Math.floor(this.map.size.w / 2);
this.centerY = Math.floor(this.map.size.h / 2);
var n = this.tempCurrentX ? this.tempCurrentX : this.map.getCenter().lon / this.map.precision;
var m = this.tempCurrentY ? this.tempCurrentY : this.map.getCenter().lat / this.map.precision;
j = this.initCenterX ? this.initCenterX : n;
g = this.initCenterY ? this.initCenterY : m;
q = this.calcNumTileX(j);
o = this.calcNumTileY(g);
for (h = 0; h < this.nbTileX; h++) {
for (e = 0; e < this.nbTileY; e++) {
r = this.images[h][e];
l = q + h - i;
k = o + e - f;
this.fillImage(r, l, k);
this.updateMapImage(r)
}
}
var p = this.getLogicalScale();
var a = this.zooms[p - 1];
c = this.minidiv.style;
c.width = a * this.map.size.w + 'px';
c.height = a * this.map.size.h + 'px'
}
if (b || (this.pandiv.style.height == '')) {
this.resizeRect()
}
if (!this.panning) {
if (!this.isFixed || !this.firstRefreshDone) {
var d = this.pandiv.style;
c = this.minidiv.style;
c.top = Math.round((this.height - parseInt(c.height, 10)) / 2) + 'px';
c.left = Math.round((this.width - parseInt(c.width, 10)) / 2) + 'px';
d.top = Math.round(((this.height - parseInt(d.height, 10)) / 2 - parseInt(c.top, 10))) + 'px';
d.left = Math.round(((this.width - parseInt(d.width, 10)) / 2 - parseInt(c.left, 10))) + 'px';
this.positionCross()
} else {
this.positionRect()
}
}
this.firstRefreshDone = true
},
setAutoZoomReduction: function (a) {
this.autoZoomReduction = a
},
setRectSize: function (b, a) {
this.rectSizeFixed = true;
this.rectWidth = b;
this.rectHeight = a
},
resizeRect: function () {
var a = this.getLogicalScale();
var b = this.map.getResolution() / this.map.precision;
this.panzoomx = b / (this.calcMapDeltaX(0, 1 / this.zooms[a - 1]));
if (this.autoZoomReduction) {
while (Math.round(this.panzoomx * this.map.size.w) + 10 > (this.sizeProportion * this.width)) {
if (!this.isFixed) {
this.scales[a - 1]++;
if (this.scales[a - 1] > this.map.getNumZoomLevels()) {
this.scales[a - 1] = this.map.getNumZoomLevels();
this.zooms[a - 1] = Math.round(this.zooms[a - 1] * 80) / 100
}
} else {
this.zooms[a - 1] = Math.round(this.zooms[a - 1] * 80) / 100
}
this.panzoomx = b / (this.calcMapDeltaX(0, 1 / this.zooms[a - 1]));
this.refresh()
}
}
var c;
if (!this.rectSizeFixed) {
if (Math.round(this.panzoomx * this.map.size.w) < 1) {
this.pandiv.style.width = '1px'
} else {
this.pandiv.style.width = Math.round(this.panzoomx * this.map.size.w) + 'px'
}
c = - b / (this.calcMapDeltaY(0, 1 / this.zooms[a - 1]));
if (Math.round(c * this.map.size.h) < 1) {
this.pandiv.style.height = '1px'
} else {
this.pandiv.style.height = Math.round(c * this.map.size.h) + 'px'
}
} else {
this.pandiv.style.width = this.rectWidth + 'px';
this.pandiv.style.height = this.rectHeight + 'px'
}
},
positionRect: function () {
if (!this.panning) {
var b = this.getLogicalScale();
var e = this.zooms[b - 1];
var c = this.map.getCenter().lon / this.map.precision;
var a = this.map.getCenter().lat / this.map.precision;
var f = this.map.getNumZoomLevels() - this.scales[this.getLogicalScale() - 1];
var d = this.map.precision / this.map.getResolutionForZoom(f);
this.pandiv.style.top = Math.round((e * d * (this.initCenterY - a) + (this.height - parseInt(this.pandiv.style.height, 10)) / 2 - parseInt(this.minidiv.style.top, 10))) + 'px';
this.pandiv.style.left = Math.round((e * ( - d) * (this.initCenterX - c) + (this.width - parseInt(this.pandiv.style.width, 10)) / 2 - parseInt(this.minidiv.style.left, 10))) + 'px';
this.positionCross()
}
},
positionCross: function () {
var b = this.pandiv.style;
var a = parseInt(b.width, 10) / 2;
var f = parseInt(b.height, 10) / 2;
var e = parseInt(b.left, 10);
var d = parseInt(b.top, 10);
var c = this.crossLineWidth / 2;
this.crossdivV.style.left = a + e - c - 1 + 'px';
this.crossdivV.style.top = f + d - c - (this.crossHeight / 2) + 'px';
this.crossdivH.style.left = a + e - c - (this.crossWidth / 2) + 'px';
this.crossdivH.style.top = f + d - c - 1 + 'px'
},
fillImage: function (e, c, b) {
e.mapTileX = c;
e.mapTileY = b;
var a = this.calcMapTileWidth();
var d = this.calcMapTileHeight();
e.mapx = Math.round(a * c + a / 2);
e.mapy = Math.round(d * b + d / 2);
this.positionImage(e);
e.nosrc = true;
e.visible = false
},
calcPixelX: function (b) {
var d = this.initCenterX ? this.initCenterX : this.map.getCenter().lon / this.map.precision;
var e = this.map.size.w / 2;
var a = this.map.getNumZoomLevels() - this.scales[this.getLogicalScale() - 1];
var c = this.map.precision / this.map.getResolutionForZoom(a);
return Math.round(e + (b - d) * c)
},
calcPixelY: function (b) {
var d = this.initCenterY ? this.initCenterY : this.map.getCenter().lat / this.map.precision;
var e = this.map.size.h / 2;
var a = this.map.getNumZoomLevels() - this.scales[this.getLogicalScale() - 1];
var c = - this.map.precision / this.map.getResolutionForZoom(a);
return Math.round(e + (b - d) * c)
},
positionImage: function (b) {
var c = this.getLogicalScale();
var a = this.zooms[c - 1];
b.posx = a * (this.calcPixelX(0) + this.tileWidth * b.mapTileX);
b.posy = a * (this.calcPixelY(0) - this.tileHeight * b.mapTileY - this.tileHeight)
},
updateMapImage: function (c) {
var d = true;
var b = 2;
var a = 2;
var f;
if (!(new OpenLayers.Bounds(c.posx, c.posy, c.posx + this.tileWidth, c.posy + this.tileHeight)).intersectsBounds(new OpenLayers.Bounds( - b, - a, this.map.size.w + b, this.map.size.h + a))) {
d = false
}
if (!d) {
if (c.visible) {
this.clearImage(c)
}
} else {
var e = this.getLogicalScale();
if (c.nosrc) {
if (!this.isVisible() || e > this.maxscale || e < this.minscale || !new OpenLayers.Bounds(this.tileLimits[0], this.tileLimits[2], this.tileLimits[1], this.tileLimits[3]).contains(c.mapTileX, c.mapTileY)) {
c.image.src = this.defaultImg;
c.loadImage = null
} else {
f = this.calcTileSrc(c);
c.image.src = f
}
c.nosrc = false
}
if (!c.visible) {
c.image.style.display = '';
c.visible = true
}
c.image.style.left = c.posx + 'px';
c.image.style.top = c.posy + 'px'
}
},
correctMapImage: function (c) {
var d = 0;
var b = 0;
var a = 0;
if (c.posx > this.map.size.w + this.marginX + a) {
d = - this.nbTileX
} else {
if (c.posx < - this.marginX - this.tileWidth - a) {
d = this.nbTileX
}
}
if (c.posy > this.map.size.h + this.marginY + a) {
b = - this.nbTileY
} else {
if (c.posy < - this.marginY - this.tileHeight - a) {
b = this.nbTileY
}
}
if (d !== 0 || b !== 0) {
this.fillImage(c, c.mapTileX + d, c.mapTileY - b);
this.clearImage(c)
}
this.updateMapImage(c)
},
calcGlobalViewPositions: function () {
if (this.initialx < 0) {
this.posx = this.map.size.w + this.initialx - this.width - 1
}
if (this.initialy < 0) {
this.posy = this.map.size.h + this.initialy - this.height - 1
}
this.div.style.left = this.posx + 'px';
this.div.style.top = this.posy + 'px'
},
getXposition: function (a) {
if (window.event) {
return window.event.clientX + document.documentElement.scrollLeft + document.body.scrollLeft
} else {
return (a.clientX || a.x) + (window.scrollX || 0)
}
},
getYposition: function (a) {
if (window.event) {
return window.event.clientY + document.documentElement.scrollTop + document.body.scrollTop
} else {
return (a.clientY || a.y) + (window.scrollY || 0)
}
},
centerMap: function (b) {
var h = this.map.getCenter().lon / this.map.precision;
var g = this.map.getCenter().lat / this.map.precision;
var p = this.initCenterX ? this.initCenterX : h;
var m = this.initCenterY ? this.initCenterY : g;
var l = this.getXposition(b);
var e = this.getYposition(b);
var f = OpenLayers.Util.pagePosition(this.div) [0];
var a = OpenLayers.Util.pagePosition(this.div) [1];
var d = f + this.width / 2;
var c = a + this.height / 2;
var q = l - d;
var o = e - c;
var i = this.getLogicalScale();
var n = this.zooms[i - 1];
var j = this.calcMapDeltaX(p, q / n) * this.map.precision;
var k = this.calcMapDeltaY(m, o / n) * this.map.precision;
this.map.panTo(new OpenLayers.LonLat(j, k))
},
remove: function (a) {
if (!this.outsideViewport) {
this.div.parentNode.removeChild(this.div)
} else {
this.clear()
}
},
clear: function () {
this.div.removeChild(this.minidiv);
this.div.removeChild(this.divIco)
},
animateView: function () {
var a = new GCUI.MapAnimator.GlobalView(this, 800);
a.play()
},
destroy: function () {
if (this.handlers.drag) {
this.handlers.drag.destroy()
}
if (this.rectEvents) {
this.rectEvents.destroy();
this.rectEvents = null
}
if (this.divEvents) {
this.divEvents.destroy();
this.divEvents = null
}
this.map.events.un({
moveend: this.refresh,
scope: this
});
OpenLayers.Control.prototype.destroy.apply(this, [
])
},
CLASS_NAME: 'GCUI.Control.GlobalView'
});
GCUI.MapAnimator = {
};
GCUI.MapAnimator.GlobalView = OpenLayers.Class({
initialize: function (a, b) {
this.gv = a;
this.totalTime = b;
this.startTime = (new Date()).getTime();
this.endTime = this.startTime + b;
GCUI.MapAnimator.current = this;
return this
},
animate: function () {
if (this.finished) {
return
}
var c = (new Date()).getTime();
if (c > this.endTime || this.gv.width < 0 || this.gv.width > this.gv.initialw || this.gv.height < 0 || this.gv.height > this.gv.initialh) {
this.stop();
return
}
var d = (c - this.startTime) / this.totalTime;
if (d > 1) {
d = 1
}
var b = this.gv.initialw * d;
var a = this.gv.initialh * d;
if (this.gv.visible) {
this.gv.width = (this.gv.width - b);
this.gv.height = (this.gv.height - a);
if (!this.gv.outsideViewport) {
this.gv.calcGlobalViewPositions()
}
if (this.gv.width > 0 && this.gv.height > 0) {
this.gv.div.style.width = this.gv.width + 'px';
this.gv.div.style.height = this.gv.height + 'px'
}
} else {
this.gv.width = (this.gv.width + b);
this.gv.height = (this.gv.height + a);
if (!this.gv.outsideViewport) {
this.gv.calcGlobalViewPositions()
}
if (this.gv.width < this.gv.initialw && this.gv.height < this.gv.initialh) {
this.gv.div.style.width = this.gv.width + 'px';
this.gv.div.style.height = this.gv.height + 'px'
}
}
},
stop: function () {
this.finished = true;
if (this.gv.visible) {
this.gv.visible = false;
this.gv.width = parseInt(this.gv.divIco.clientWidth, 10);
this.gv.height = parseInt(this.gv.divIco.clientHeight, 10);
this.gv.div.style.width = this.gv.divIco.clientWidth + 'px';
this.gv.div.style.height = this.gv.divIco.clientHeight + 'px';
this.gv.divIco.className = 'mapglobalviewico2'
} else {
this.gv.visible = true;
this.gv.width = parseInt(this.gv.initialw, 10);
this.gv.height = parseInt(this.gv.initialh, 10);
this.gv.div.style.width = this.gv.initialw + 'px';
this.gv.div.style.height = this.gv.initialh + 'px';
this.gv.divIco.className = 'mapglobalviewico';
this.gv.refresh()
}
if (!this.gv.outsideViewport) {
this.gv.calcGlobalViewPositions()
}
},
isFinished: function () {
return this.finished
},
play: function (a) {
if (!a) {
a = 1
}
if (!this.isFinished()) {
this.animate();
window.setTimeout('GCUI.MapAnimator.current.play(' + a + ');', a)
}
}
});
GCUI.Control.OverviewMap = OpenLayers.Class(OpenLayers.Control.OverviewMap, {
size: {
w: 150,
h: 150
},
minRatio: 10,
maxRatio: 20,
autoPan: true,
maximized: true,
createMap: function () {
var b = OpenLayers.Util.extend({
controls: [
],
maxResolution: 'auto',
fallThrough: false
}, this.mapOptions);
this.ovmap = new GCUI.Map(this.mapDiv, b);
this.ovmap.viewPortDiv.appendChild(this.extentRectangle);
OpenLayers.Event.stopObserving(window, 'unload', this.ovmap.unloadDestroy);
this.ovmap.addLayers(this.layers);
this.ovmap.zoomToMaxExtent();
this.wComp = parseInt(OpenLayers.Element.getStyle(this.extentRectangle, 'border-left-width')) + parseInt(OpenLayers.Element.getStyle(this.extentRectangle, 'border-right-width'));
this.wComp = (this.wComp) ? this.wComp : 2;
this.hComp = parseInt(OpenLayers.Element.getStyle(this.extentRectangle, 'border-top-width')) + parseInt(OpenLayers.Element.getStyle(this.extentRectangle, 'border-bottom-width'));
this.hComp = (this.hComp) ? this.hComp : 2;
this.handlers.drag = new OpenLayers.Handler.Drag(this, {
move: this.rectDrag,
done: this.updateMapToRect
}, {
map: this.ovmap
});
this.handlers.click = new OpenLayers.Handler.Click(this, {
click: this.mapDivClick
}, {
single: true,
'double': false,
stopSingle: true,
stopDouble: true,
pixelTolerance: 1,
map: this.ovmap
});
this.handlers.click.activate();
this.rectEvents = new OpenLayers.Events(this, this.extentRectangle, null, true);
this.rectEvents.register('mouseover', this, function (d) {
if (!this.handlers.drag.active && !this.map.dragging) {
this.handlers.drag.activate()
}
});
this.rectEvents.register('mouseout', this, function (d) {
if (!this.handlers.drag.dragging) {
this.handlers.drag.deactivate()
}
});
if (this.ovmap.getProjection() != this.map.getProjection()) {
var c = this.map.getProjectionObject().getUnits() || this.map.units || this.map.baseLayer.units;
var a = this.ovmap.getProjectionObject().getUnits() || this.ovmap.units || this.ovmap.baseLayer.units;
this.resolutionFactor = c && a ? OpenLayers.INCHES_PER_UNIT[c] / OpenLayers.INCHES_PER_UNIT[a] : 1
}
},
CLASS_NAME: 'GCUI.Control.OverviewMap'
});
GCUI.Control.GeoConceptGetFeatureInfo = OpenLayers.Class(OpenLayers.Control, {
hover: false,
drillDown: false,
maxFeatures: 10,
clickCallback: 'click',
layers: null,
queryVisible: true,
infoFormat: 'text/html',
handlerOptions: null,
handler: null,
hoverRequest: null,
EVENT_TYPES: [
'beforegetfeatureinfo',
'getfeatureinfo',
'exception'
],
pending: 0,
infoMode: 'infoboxfields',
initialize: function (a) {
this.EVENT_TYPES = this.EVENT_TYPES.concat(OpenLayers.Control.prototype.EVENT_TYPES);
a = a || {
};
a.handlerOptions = a.handlerOptions || {
};
OpenLayers.Control.prototype.initialize.apply(this, [
a
]);
if (this.drillDown === true) {
this.hover = false
}
if (this.hover) {
this.handler = new OpenLayers.Handler.Hover(this, {
move: this.cancelHover,
pause: this.getInfoForHover
}, this.handlerOptions.hover || {
})
} else {
var b = {
};
b[this.clickCallback] = this.getInfoForClick;
this.handler = new OpenLayers.Handler.Click(this, b, this.handlerOptions.click || {
})
}
},
getInfoForClick: function (a) {
this.request(a.xy, {
})
},
getInfoForHover: function (a) {
if (this.hoverXY && this.hoverXY.x == a.xy.x && this.hoverXY.y == a.xy.y) {
return
}
this.hoverXY = a.xy;
this.request(a.xy, {
hover: true
})
},
cancelHover: function () {
if (this.hoverRequest) {
--this.pending;
if (this.pending <= 0) {
OpenLayers.Element.removeClass(this.map.viewPortDiv, 'olCursorWait');
this.pending = 0
}
}
},
findLayers: function () {
var c = this.layers || this.map.layers;
var d = [
];
var b;
for (var a = c.length - 1; a >= 0; --a) {
b = c[a];
if (b.CLASS_NAME === 'GCUI.Layer.GeoConcept' && (!this.queryVisible || b.getVisibility()) && b.opacity > 0) {
d.push(b);
if (!this.drillDown || this.hover) {
break
}
}
}
return d
},
buildRequestOptions: function (a, b) {
var c = this.map.getLonLatFromPixel(b);
return {
url: a.getUrlGC() + '/api/gcis/json/feature/',
loc: c
}
},
request: function (g, c) {
c = c || {
};
var f = this.findLayers();
if (f.length > 0) {
var b,
e;
for (var d = 0, a = f.length; d < a; d++) {
e = f[d];
b = this.triggerBefore(g, e);
if (b !== false) {
++this.pending;
var h = this.getProtocol(e, g);
if (c.hover === true) {
this.hoverRequest = h
}
h.read()
}
}
if (this.pending > 0) {
OpenLayers.Element.addClass(this.map.viewPortDiv, 'olCursorWait')
}
}
},
triggerBefore: function (b, a) {
return this.events.triggerEvent('beforegetfeatureinfo', {
xy: b,
layer: a
})
},
getOffsetX: function (a) {
return (a.singleTile ? (a.tileSize.w - a.tileSize.w / a.ratio) / 2 : 0)
},
getOffsetY: function (a) {
return (a.singleTile ? (a.tileSize.h - a.tileSize.h / a.ratio) / 2 : 0)
},
createProtocol: function (a, d, b, c, e) {
return new OpenLayers.Protocol.Script({
url: a,
params: d,
handleResponse: function (g, f) {
if (g.data && g.data.status === 'OK') {
g.responseText = g.data.result;
g.code = OpenLayers.Protocol.Response.SUCCESS
} else {
g.status = g.data.http ? g.data.http.code || 400 : 400;
g.statusText = g.data ? g.data.message || '' : '';
g.code = OpenLayers.Protocol.Response.FAILURE
}
this.destroyRequest(g.priv);
f.callback.call(f.scope, g)
},
callback: e || function (f) {
this.handleResponse(c, f, b)
},
scope: this
})
},
getProtocol: function (b, d) {
var a = this.buildRequestOptions(b, d);
var c = (this.serviceFormat === 'html') ? 'html' : 'text';
return this.createProtocol(a.url + 'info/' + c, {
mapName: b.mapname,
tabName: b.tabname,
x: a.loc.lon,
y: a.loc.lat,
infoMode: this.infoMode,
scale: this.map.getLogicalScale()
}, b, d)
},
handleResponse: function (c, b, a) {
--this.pending;
if (this.pending <= 0) {
OpenLayers.Element.removeClass(this.map.viewPortDiv, 'olCursorWait');
this.pending = 0
}
if (b.code === OpenLayers.Protocol.Response.FAILURE) {
this.events.triggerEvent('exception', {
xy: c,
request: b,
layer: a
})
} else {
this.events.triggerEvent('getfeatureinfo', {
text: b.responseText,
request: b,
xy: c,
layer: a
})
}
},
getFeature: function (c) {
var f = this.findLayers();
if (f.length > 0) {
for (var e = 0, a = f.length;
e < a; e++) {
var d = f[e];
var b = this.triggerBefore(null, d);
if (b !== false) {
var h = OpenLayers.Util.extend({
url: d.getUrlGC() + '/api/gcis/json/feature/get',
params: {
  mapName: d.mapname,
  userSessionID: d.userId,
  featureID: c.featureId,
  type: this.type,
  subtype: this.subtype
},
handleResponse: function (j, i) {
  this.destroyRequest(j.priv);
  i.callback.call(i.scope, j.data, i)
},
scope: this
}, c);
var g = new OpenLayers.Protocol.Script(h);
g.read()
}
}
}
},
CLASS_NAME: 'GCUI.Control.GeoConceptGetFeatureInfo'
});
GCUI.Control.GeoConceptSelectFeature = OpenLayers.Class(GCUI.Control.GeoConceptGetFeatureInfo, {
type: null,
subtype: null,
EVENT_TYPES: [
'beforefeatureselected',
'featureselected',
'exception',
'zoomOnSelection'
],
selectedFeatures: [
],
box: false,
selectOperation: 'xor',
strictInclusion: true,
layers: null,
maxSelectedData: null,
initialize: function (a) {
GCUI.Control.GeoConceptGetFeatureInfo.prototype.initialize.apply(this, [
a
]);
if (this.box) {
this.handler = new OpenLayers.Handler.Box(this, {
done: this.selectBox
}, {
boxDivClassName: 'olHandlerBoxSelectFeature'
})
}
if (this.polygon) {
var b = {
done: this.selectByPolygon
};
this.callbacks = OpenLayers.Util.extend(b, this.callbacks);
this.handlerOptions = OpenLayers.Util.extend({
persist: this.persist
}, this.handlerOptions);
this.handler = new OpenLayers.Handler.Polygon(this, this.callbacks, this.handlerOptions)
}
},
triggerBefore: function (b, a) {
return this.events.triggerEvent('beforefeatureselected', {
xy: b,
layer: a
})
},
getProtocolParams: function (a) {
return {
op: this.selectOperation,
mapName: a.mapname,
userSessionID: a.userId,
fields: this.dataFields.join(','),
strict: this.strictInclusion
}
},
getProtocol: function (a, b) {
var c = this.getProtocolParams(a);
c.clickX = this.getOffsetX(a) + b.x;
c.clickY = this.getOffsetY(a) + b.y;
return this.createProtocol(a.getUrlGC() + '/api/gcis/json/feature/selectByPoint', c, a, b)
},
handleResponse: function (c, b, a) {
--this.pending;
if (this.pending <= 0) {
OpenLayers.Element.removeClass(this.map.viewPortDiv, 'olCursorWait');
this.pending = 0
}
if (b.code === OpenLayers.Protocol.Response.FAILURE) {
this.events.triggerEvent('exception', {
xy: c,
request: b,
layer: a
})
} else {
this.selectedFeatures = b.responseText;
this.events.triggerEvent('featureselected', {
text: b.responseText,
request: b,
xy: c,
layer: a
})
}
a.redraw()
},
selectByIds: function (a) {
var g = '';
for (var e = 0; e < a.length; e++) {
g += this.keyField + ';' + a[e] + ','
}
var d = this.findLayers();
if (d.length > 0) {
for (var e = 0, h = d.length; e < h; e++) {
var f = d[e];
var j = this.triggerBefore(null, f);
if (j !== false) {
var c = this.getProtocolParams(f);
c.type = this.type;
c.subtype = this.subtype;
c.criteria = g;
c.operator = 'or';
var b = this.createProtocol(f.getUrlGC() + '/api/gcis/json/features/select', c, f);
b.read()
}
}
}
},
clearSelection: function () {
var e = this.findLayers();
if (e.length > 0) {
for (var c = 0, a = e.length; c < a; c++) {
var b = e[c];
var d = this.createProtocol(b.getUrlGC() + '/api/gcis/json/features/select/clear', {
userSessionID: b.userId
}, b);
d.read()
}
}
},
zoomOnSelection: function () {
var e = this.findLayers();
if (e.length > 0) {
for (var c = 0, a = e.length;
c < a; c++) {
var b = e[c];
var f = {
mapName: b.mapname,
userSessionID: b.userId,
zoomOnSelection: true,
sizeX: b.tileSize.w / b.ratio,
sizeY: b.tileSize.h / b.ratio,
tick: new Date().getTime()
};
var d = this.createProtocol(b.getUrlGC() + '/api/gcis/json/currentPosition', f, b, null, function (h) {
var g = h.responseText;
b.map.setCenter(new OpenLayers.LonLat(g.posX / 100, g.posY / 100), 12 - g.scale);
this.events.triggerEvent('zoomOnSelection', {
lon: g.posX / 100,
lat: g.posY / 100,
z: 12 - g.scale
})
});
d.read()
}
}
},
selectBox: function (h) {
if (h instanceof OpenLayers.Bounds) {
var g = this.findLayers();
var i;
for (var e = 0; e < g.length; ++e) {
i = g[e];
var d = this.getOffsetX(i) + h.left;
var k = this.getOffsetY(i) + h.bottom;
var b = this.getOffsetX(i) + h.right;
var j = this.getOffsetY(i) + h.top;
var a = i.getUrlGC() + '/api/gcis/json/feature/selectByPoly';
var f = this.getProtocolParams(i);
f.clickMap = [
[d,
k].join(','),
[
d,
j
].join(','),
[
b,
j
].join(','),
[
b,
k
].join(','),
[
d,
k
].join(',')
].join(';');
var c = this.createProtocol(a, f, i);
c.read()
}
}
},
selectByPolygon: function (q) {
var j = q.components[0];
var m = j.components;
var n = m.length;
var e = [
];
for (var h = 0; h < n; ++h) {
var o = m[h];
var s = this.map.getPixelFromLonLat(new OpenLayers.LonLat(o.x, o.y));
e.push(s)
}
var g = this.findLayers();
var k;
for (var d = 0; d < g.length; ++d) {
k = g[d];
var r = [
];
for (h = 0; h < n; ++h) {
r.push((this.getOffsetX(k) + e[h].x) + ',' + (this.getOffsetY(k) + e[h].y))
}
var a = k.getUrlGC() + '/api/gcis/json/feature/selectByPoly';
var f = this.getProtocolParams(k);
f.clickMap = r.join(';');
if (this.maxSelectedData) {
f.dataRange = '1,' + this.maxSelectedData
}
var b = this.createProtocol(a, f, k);
b.read()
}
},
CLASS_NAME: 'GCUI.Control.GeoConceptSelectFeature'
});
GCUI.Control.Route = OpenLayers.Class(OpenLayers.Control, {
layer: null,
simplifiedMinScale: 8,
simplifiedMaxScale: 12,
routes: [
],
setMap: function (a) {
OpenLayers.Control.prototype.setMap.apply(this, [
a
]);
this.map.events.register('zoomend', this, this.updateFeatures)
},
route: function (b) {
var d = [
];
var a = b.waypoints ? b.waypoints.length : 0;
for (var c = 0; c < a; c++) {
d.push(b.waypoints[c].toShortString())
}
var f = OpenLayers.Util.extend({
url: this.map ? this.map.baseLayer.getUrlGC() + '/api/lbs/route/json' : '',
params: {
origin: b.origin.toShortString(),
destination: b.destination.toShortString(),
tolerance: b.tolerance || 100,
waypoints: d.join(';')
},
handleResponse: function (h, g) {
this.destroyRequest(h.priv);
g.callback.call(g.scope, h.data, g)
},
scope: this
}, b);
if (b.graph) {
f.params.graph = b.graph
}
var e = new OpenLayers.Protocol.Script(f);
e.read()
},
displayRoute: function (w, b) {
if (w.errors !== undefined || w.legs.length === 0) {
return null
}
if (!this.layer) {
this.layer = new OpenLayers.Layer.Vector('routeLayer' + this.id, {
displayInLayerSwitcher: false,
style: {
strokeColor: '#1BE01B',
strokeWidth: 2
}
});
this.map.addLayer(this.layer)
}
var e = [
];
var c = (b && b.style) ? b.style : null;
for (var t = 0, h = w.legs.length; t < h; ++t) {
var m = w.legs[t];
for (var l = 0, v = m.steps.length; l < v; l++) {
var f = m.steps[l];
var q = [
];
for (var n = 0, k = f.points.length; n < k; n++) {
var o = OpenLayers.LonLat.fromString(f.points[n]);
q.push(new OpenLayers.Geometry.Point(o.lon, o.lat))
}
var g = OpenLayers.Util.extend({
duration: f.duration,
distance: f.distance,
navInstruction: f.navInstruction,
name: f.name
}, b ? b.data : {
});
var d = new OpenLayers.Feature.Vector(new OpenLayers.Geometry.LineString(q), g, c);
e.push(d)
}
}
var u = new OpenLayers.Format.WKT().read(w.simplifiedWkt);
u.style = c;
u.attributes = OpenLayers.Util.extend({
duration: w.duration,
distance: w.distance
}, b ? b.data : {
});
var a = this.map.getLogicalScale();
if ((this.simplifiedMinScale <= a) && (this.simplifiedMaxScale >= a)) {
this.layer.addFeatures([u])
} else {
this.layer.addFeatures(e)
}
var j = {
features: e,
simplifiedFeature: u
};
this.routes.push(j);
return j
},
updateFeatures: function () {
if (!this.layer) {
return
}
var d = this.map.getLogicalScale();
this.layer.removeAllFeatures();
if ((this.simplifiedMinScale <= d) && (this.simplifiedMaxScale >= d)) {
var c = [
];
for (var b = 0, a = this.routes.length; b < a; b++) {
c.push(this.routes[b].simplifiedFeature)
}
this.layer.addFeatures(c)
} else {
for (b = 0, a = this.routes.length; b < a; b++) {
this.layer.addFeatures(this.routes[b].features)
}
}
},
CLASS_NAME: 'GCUI.Control.Route'
});
GCUI.Control.GeoCode = OpenLayers.Class(OpenLayers.Control, {
geocode: function (a) {
var c = OpenLayers.Util.extend({
url: this.map ? this.map.baseLayer.getUrlGC() + '/api/lbs/geocode/json' : '',
params: {
cc: a.countryCode,
al: a.addressLine,
pc: a.postalCode,
ci: a.city,
pr: a.projection,
mr: a.maxResponses
},
handleResponse: function (e, d) {
this.destroyRequest(e.priv);
d.callback.call(d.scope, e.data, d)
},
scope: this
}, a);
var b = new OpenLayers.Protocol.Script(c);
b.read()
},
CLASS_NAME: 'GCUI.Control.GeoCode'
});
GCUI.Control.Print = OpenLayers.Class(OpenLayers.Control, {
initialize: function (a) {
OpenLayers.Control.prototype.initialize.apply(this, [
a
])
},
draw: function (b) {
var d = OpenLayers.Control.prototype.draw.apply(this, [
b
]);
var c = document.createElement('form');
c.method = 'POST';
c.target = '_blank';
this.form = c;
var a = document.createElement('input');
a.name = 'data';
a.type = 'hidden';
this.dataInput = a;
c.appendChild(a);
d.appendChild(c);
return d
},
print: function () {
var a = this.map.toJSON();
this.dataInput.value = a;
this.form.action = this.printUrl || (this.map ? this.map.baseLayer.getUrlGC() + '/api/htc/print/image' : '');
this.form.submit()
},
CLASS_NAME: 'GCUI.Control.Print'
});
GCUI.Control.Click = OpenLayers.Class(OpenLayers.Control, {
defaultHandlerOptions: {
single: true,
'double': false,
pixelTolerance: 0,
stopSingle: false,
stopDouble: false
},
initialize: function (a) {
this.handlerOptions = OpenLayers.Util.extend({
}, this.defaultHandlerOptions);
OpenLayers.Control.prototype.initialize.apply(this, arguments);
this.handler = new OpenLayers.Handler.Click(this, {
click: this.onClick,
dblclick: this.onDblclick
}, this.handlerOptions)
},
onClick: function (a) {
},
onDblclick: function (a) {
},
CLASS_NAME: 'GCUI.Control.Click'
});
GCUI.Control.RightClick = OpenLayers.Class(OpenLayers.Control, {
defaultHandlerOptions: {
single: true,
'double': false,
pixelTolerance: 0,
stopSingle: false,
stopDouble: false
},
handleRightClicks: true,
initialize: function (a) {
this.handlerOptions = OpenLayers.Util.extend({
}, this.defaultHandlerOptions);
OpenLayers.Control.prototype.initialize.apply(this, [
a
]);
this.handler = new OpenLayers.Handler.Click(this, {
rightclick: this.onRightClick
}, this.handlerOptions)
},
CLASS_NAME: 'GCUI.Control.RightClick'
});
GCUI.Layer.Thematic = OpenLayers.Class(GCUI.Layer.GeoConcept, {
isBaseLayer: false,
transparent: true,
singleTile: true,
fieldId: 'id',
fieldValue: 'legende0',
noValueColor: 'FF0000',
type: 'color',
initialize: function (c, b, d, a) {
GCUI.Layer.GeoConcept.prototype.initialize.apply(this, [
c,
b,
d,
a
])
},
beforeInitLayer: function () {
return true
},
loadMetadata: function () {
if (this.userId) {
GCUI.Layer.GeoConcept.prototype.loadMetadata.apply(this, [
])
}
},
setMap: function (a) {
GCUI.Layer.GeoConcept.prototype.setMap.apply(this, [
a
]);
this.createThematic(OpenLayers.Function.bind(function (c) {
var b = new OpenLayers.Format.JSON().read(c.priv.responseText).result;
this.userId = b.userId;
this.map.setCenter(new OpenLayers.LonLat(b.centerX, b.centerY), this.map.getNumZoomLevels() - b.scale);
this.checkMetadata()
}, this))
},
createThematic: function (e) {
var c = this.classes;
var b = [
];
var k = [
];
for (var h = 0; h < c.length; h++) {
k = [
];
for (var f in c[h]) {
if (c[h].hasOwnProperty(f)) {
k.push(f + ':' + c[h][f])
}
}
b.push(k.join(','))
}
var g = this.data;
var j = [
];
for (h = 0; h < g.length; h++) {
k = [
];
for (f in g[h]) {
if (g[h].hasOwnProperty(f)) {
k.push(g[h][f])
}
}
j.push(k.join(','))
}
var d = {
mapName: this.getMapName(),
tabName: this.tabname,
sizeX: this.map.size.w,
sizeY: this.map.size.h,
fieldId: this.fieldId,
fieldValue: this.fieldValue,
noValueColor: this.noValueColor,
classes: b.join(';'),
thematicName: this.name,
gcType: this.gcType,
gcSubType: this.gcSubType,
data: j.join(';'),
tick: new Date().getTime()
};
var a = new OpenLayers.Protocol.HTTP({
url: this.getUrlGC() + '/api/gcis/thematic/json/' + this.type,
params: d,
handleResponse: function (l, i) {
i.callback.call(i.scope, l)
},
callback: function (i) {
e(i)
},
scope: this,
readWithPOST: true
});
a.read()
},
getLegendUrl: function (a) {
var b = OpenLayers.Util.extend({
uid: this.userId
}, a);
return this.getUrlGC() + '/api/gcis/thematic/image/legend?' + OpenLayers.Util.getParameterString(b)
},
CLASS_NAME: 'GCUI.Layer.Thematic'
});
GCUI.Popup = GCUI.Popup || {
};
GCUI.Popup.Anchored = new OpenLayers.Class(OpenLayers.Popup.Anchored, {
contentDisplayClass: 'gcuiAnchoredContent',
initialize: function (h, d, g, c, f, e, b) {
var a = [
h,
d,
g,
c,
f,
e
];
OpenLayers.Popup.prototype.initialize.apply(this, a);
this.anchor = (b != null) ? b : {
size: new OpenLayers.Size( - 40, 40),
offset: new OpenLayers.Pixel(20, - 20)
};
h = this.div.id + '_bottomAnchorDiv';
this.bottomAnchorDiv = OpenLayers.Util.createDiv(h, null, null, null, 'relative');
this.div.appendChild(this.bottomAnchorDiv);
h = this.div.id + '_topAnchorDiv';
this.topAnchorDiv = OpenLayers.Util.createDiv(h, null, null, null, 'relative');
this.div.insertBefore(this.topAnchorDiv, this.groupDiv);
this.groupDiv.className = 'gcuiPopupGroupDiv';
this.useImg = (parseFloat(navigator.appVersion.split('MSIE') [1]) < 10)
},
setSize: function (a) {
OpenLayers.Popup.Anchored.prototype.setSize.apply(this, [
a
]);
this.div.style.height = (parseInt(this.div.style.height) + this.anchor.offset.x) + 'px'
},
getContentDivPadding: function () {
var a = this._contentDivPadding;
if (!a) {
this.div.style.display = 'none';
document.body.appendChild(this.div);
a = new OpenLayers.Bounds(OpenLayers.Element.getStyle(this.contentDiv, 'padding-left'), OpenLayers.Element.getStyle(this.contentDiv, 'padding-bottom'), OpenLayers.Element.getStyle(this.contentDiv, 'padding-right'), OpenLayers.Element.getStyle(this.contentDiv, 'padding-top'));
this._contentDivPadding = a;
if (this.div.parentNode == document.body) {
document.body.removeChild(this.div);
this.div.style.display = ''
}
}
return a
},
updateRelativePosition: function () {
var b = (this.relativePosition.charAt(0) == 't');
this.bottomAnchorDiv.className = 'anchorDivBottom' + (this.useImg ? 'Img' : this.relativePosition.charAt(1));
this.topAnchorDiv.className = 'anchorDivTop' + (this.useImg ? 'Img' : this.relativePosition.charAt(1));
if (b) {
this.topAnchorDiv.style.display = 'none';
this.bottomAnchorDiv.style.display = 'block'
} else {
this.topAnchorDiv.style.display = 'block';
this.bottomAnchorDiv.style.display = 'none'
}
var a = 0;
if (this.div.style.width !== '') {
a = parseInt(this.div.style.width)
}
if (this.relativePosition.charAt(1) == 'l') {
this.bottomAnchorDiv.style.left = (a - 2 * this.anchor.offset.x) + 'px';
this.topAnchorDiv.style.left = (a - 2 * this.anchor.offset.x) + 'px'
} else {
this.bottomAnchorDiv.style.left = this.anchor.offset.x + 'px';
this.topAnchorDiv.style.left = this.anchor.offset.x + 'px'
}
},
moveTo: function (a) {
this.relativePosition = this.calculateRelativePosition(a);
OpenLayers.Popup.prototype.moveTo.call(this, this.calculateNewPx(a));
this.updateRelativePosition()
},
calculateNewPx: function (b) {
var e = b.offset(this.anchor.offset);
var a = this.size || this.contentSize;
var d = (this.relativePosition.charAt(0) == 't');
e.y += (d) ? - a.h : (this.anchor.size.h - this.anchor.offset.x);
var c = (this.relativePosition.charAt(1) == 'l');
e.x += (c) ? - a.w : (this.anchor.size.w - this.anchor.offset.x);
return e
},
setBackgroundColor: function () {
},
CLASS_NAME: 'GCUI.Popup.Anchored'
});
OpenLayers.Control.LoadingPanel = OpenLayers.Class(OpenLayers.Control, {
counter: 0,
maximized: false,
onlySingleTile: true,
initialize: function (a) {
OpenLayers.Control.prototype.initialize.apply(this, [
a
])
},
toggle: function () {
this.setVisible(!this.getVisible())
},
addLayer: function (a) {
var b = a.layer;
if (this.checkLayer(b)) {
b.events.register('loadstart', this, this.increaseCounter);
b.events.register('loadend', this, this.decreaseCounter)
}
},
checkLayer: function (a) {
return a && (this.onlySingleTile ? a.singleTile : true)
},
setMap: function (b) {
OpenLayers.Control.prototype.setMap.apply(this, [
b
]);
this.map.events.register('preaddlayer', this, this.addLayer);
for (var a = 0; a < this.map.layers.length; a++) {
this.addLayer({
layer: this.map.layers[a]
})
}
},
increaseCounter: function () {
this.counter++;
if (this.counter > 0) {
this.show()
}
},
decreaseCounter: function () {
if (this.counter > 0) {
this.counter--
}
if (this.counter === 0) {
this.hide()
}
},
hide: function (a) {
this.div.style.display = 'none';
this.maximized = false;
if (a != null) {
OpenLayers.Event.stop(a)
}
},
show: function (a) {
this.div.style.display = 'block';
this.maximized = true;
if (a != null) {
OpenLayers.Event.stop(a)
}
},
destroy: function () {
if (this.map) {
this.map.events.unregister('preaddlayer', this, this.addLayer);
if (this.map.layers) {
for (var b = 0; b < this.map.layers.length; b++) {
var a = this.map.layers[b];
a.events.unregister('loadstart', this, this.increaseCounter);
a.events.unregister('loadend', this, this.decreaseCounter)
}
}
}
OpenLayers.Control.prototype.destroy.apply(this, [
])
},
CLASS_NAME: 'OpenLayers.Control.LoadingPanel'
});
GCUI.Map.Version = '2.4.3';
OpenLayers._getScriptLocation = (function () {
var a = function (j) {
var h = document.getElementsByTagName('script'),
k,
e,
f = '';
for (var g = 0, d = h.length; g < d; g++) {
k = h[g].getAttribute('src');
if (k) {
e = k.match(j);
if (e) {
f = e[1];
break
}
}
}
return f
};
var c = new RegExp('(^|(.*?\\/))((lib/OpenLayers/SingleFile).js)(\\?|$)');
var b = a(c);
if (b === '') {
c = /^([^\?]+)\?.*gcui-htc\/htc(-min)?\.js/;
b = a(c)
}
if (b === '') {
c = new RegExp('(^|(.*?\\/))((htc(-min|-debug|-nocompress|)?).js)(\\?|$)');
b = a(c)
}
return (function () {
return b
})
}) ();
function DynMapCreate(b, s, d, l, k, h, f, r, a, j, o, e, n, g, m, i, c, q) {
var p = new GCUI.Map(d.id, {
center: new OpenLayers.LonLat(h, f),
precision: o,
initialCenter: new OpenLayers.LonLat(h, f),
initialScale: r,
server: l,
document: s,
window: b,
mapName: k,
scale: r,
tab: a,
ratios: j,
limits: e,
format: n,
showSlider: g,
tilewidth: m,
tileheight: i,
version: c,
layer: q
});
return p
}
function DynMapGetMap(b, a) {
if (!b) {
b = document
}
return b.maps ? b.maps[a] : null
}
function DynMapMaximize(b, a, c) {
b.maximize(a, c)
}
function DynMapSetMinimumScale(a, b) {
a.setMinLogicalScale(b)
}
function DynMapSetMaximumScale(a, b) {
a.setMaxLogicalScale(b)
}
function DynMapRefresh(b, a) {
if (b && b.objectLayer) {
b.objectLayer.refresh(a)
}
}
function DynMapSetMouseMode(a, f) {
if (!f) {
return
}
var g = a.getControlsByClass('OpenLayers.Control.DrawFeature');
for (var e = 0; e < g.length; e++) {
g[e].destroy()
}
var c = a.getControlsByClass('OpenLayers.Control.ZoomBox');
for (e = 0; e < c.length; e++) {
c[e].destroy()
}
var n = a.getControlsByClass('OpenLayers.Control.Measure');
for (e = 0; e < n.length; e++) {
n[e].destroy()
}
if (f === 1 || f.name === 'moveMode' || f === 2) {
return
}
if (f.name === 'unzoom') {
DynMapSetMouseCursor(a, 'crosshair');
var d = new OpenLayers.Control.ZoomBox({
autoActivate: true,
zoomBox: function (i) {
if (i instanceof OpenLayers.Bounds) {
var o = i.getCenterPixel();
i = this.map.getLonLatFromPixel(o)
}
this.map.moveTo(i, this.map.getZoom() - 3)
}
});
a.addControl(d);
return
}
if (f === 4) {
DynMapSetMouseCursor(a, 'crosshair');
d = new OpenLayers.Control.ZoomBox({
autoActivate: true
});
a.addControl(d)
} else {
var b = a.getLayersByName(f.handlerOptions.layerName);
b[0].style = f.handlerOptions.layerStyle;
if (f.name === 'createMode') {
var h = {
irregular: f.handlerOptions.irregular,
sides: f.handlerOptions.sides
};
var m = new OpenLayers.Control.DrawFeature(b[0], f.handlerOptions.handlerType, {
handlerOptions: h
});
a.addControl(m);
m.activate();
var k = OpenLayers.Function.bind(function (p) {
p.feature.kind = f.kind;
if (this.drawingListeners) {
for (var i in this.drawingListeners) {
if (this.drawingListeners.hasOwnProperty(i)) {
  this.drawingListeners[i].obj.onDrawFinish(p.feature, f.kind)
}
}
}
}, a);
m.events.register('featureadded', a, k)
} else {
if (f.name === 'distanceMode') {
var j = 0;
var l = new OpenLayers.Control.Measure(OpenLayers.Handler.Path, {
persist: true,
handlerOptions: {
style: f.handlerOptions.layerStyle
},
textNodes: null,
callbacks: {
create: function () {
  this.textNodes = [
  ];
  b[0].removeFeatures(b[0].features);
  j = 0
},
modify: function (w, x) {
  if (!x.style) {
    x.style = f.handlerOptions.layerStyle;
    x.kind = 'line'
  }
  if (j++ < 5) {
    return
  }
  var p = x.geometry.components.length;
  var u = x.geometry.components[p - 2];
  var v = x.geometry.components[p - 1];
  var o = new OpenLayers.Geometry.LineString([u,
  v]);
  var r = this.getBestLength(o);
  if (!r[0]) {
    return
  }
  var s = this.getBestLength(x.geometry);
  var t = r[0].toFixed(2) + ' ' + r[1];
  var i = this.textNodes[p - 2] || null;
  if (i && !i.layer) {
    this.textNodes.pop();
    i = null
  }
  if (!i) {
    var q = o.getCentroid();
    i = new OpenLayers.Feature.Vector(new OpenLayers.Geometry.Point(q.x, q.y), {
    }, {
      label: '',
      fontColor: '#FF0000',
      fontSize: '14px',
      fontFamily: 'Arial',
      fontWeight: 'bold',
      labelAlign: 'cm'
    });
    this.textNodes.push(i);
    b[0].addFeatures([i])
  }
  i.geometry.x = (u.x + v.x) / 2;
  i.geometry.y = (u.y + v.y) / 2;
  i.style.label = t;
  i.layer.drawFeature(i);
  if (f.handlerOptions.divseg != null) {
    f.handlerOptions.divseg.innerHTML = t
  }
  if (f.handlerOptions.divtot != null) {
    f.handlerOptions.divtot.innerHTML = s[0].toFixed(2) + ' ' + s[1]
  }
}
}
});
l.events.on({
measure: function (i) {
f.handlerOptions.divtot.innerHTML = i.measure.toFixed(2) + ' ' + i.units
}
});
a.addControl(l);
l.activate()
}
}
}
}
function DynMapGetMouseMode() {
}
function DynMapCenterOnRect(g, b, d, a, c, h) {
var f = g.precision;
if (!h) {
b *= f;
a *= f;
d *= f;
c *= f
}
var e = new OpenLayers.Bounds(b, d, a, c);
g.zoomToExtent(e)
}
function DynMapCenter(c, a, d, b) {
if (!b) {
a *= c.precision;
d *= c.precision
}
c.setCenter(new OpenLayers.LonLat(a, d))
}
function DynMapInitialCenter(a) {
a.setCenter(a.initialCenter, a.getNumZoomLevels() - a.initialScale)
}
function DynMapCenterClick() {
}
function DynMapSetScale(b, a) {
b.setCenter(null, b.getNumZoomLevels() - a)
}
function DynMapSetSize(c, a, b) {
c.setSize(a + 'px', b + 'px')
}
function DynMapGetWidth(a) {
return a.size.w
}
function DynMapGetHeight(a) {
return a.size.h
}
function DynMapResize(a) {
a.updateSize()
}
function DynMapGetBoundingBox(b) {
var c = function (d) {
return b.getLonLatFromPixel(new OpenLayers.Pixel(d, 0)).lon / b.precision
};
var a = function (d) {
return b.getLonLatFromPixel(new OpenLayers.Pixel(0, d)).lat / b.precision
};
return [c(0),
a(0),
c(b.size.w),
a(b.size.h)]
}
function DynMapShowPoint(a, k, j, d, h, b) {
if (b === null || !b) {
var f = a.getLayersByClass('OpenLayers.Layer.Markers');
for (var g = 0; g < f.length; g++) {
a.removeLayer(f[g])
}
}
var e = new OpenLayers.Layer.Markers('Markers');
a.addLayer(e);
var c = new Image();
c.onload = function () {
var i = new OpenLayers.Icon(h, new OpenLayers.Size(this.width, this.height));
e.addMarker(new OpenLayers.Marker(new OpenLayers.LonLat(k, j), i));
a.setCenter(new OpenLayers.LonLat(k, j), a.getNumZoomLevels() - d)
};
c.src = h
}
function DynMapSetMouseCursor(b, a) {
b.div.style.cursor = a
}
function DynMapSetMouseCursorDown(b, a) {
}
function DynMapGetPhysicalScale(a) {
}
function DynMapResetSelection(c) {
var a = c.getControlsByClass('OpenLayers.Control.SelectFeature');
for (var b = 0; b < a.length; b++) {
a[b].unselectAll()
}
}
function DynMapGetSelection(e) {
var a = [
];
var c = e.getLayersByName('objects');
if (c[0]) {
var d = c[0].selectedFeatures;
for (var b = 0; b < d.length; b++) {
a.push(d[b].fid)
}
}
return a
}
function DynMapPixelToMapX(b, a) {
var c = a - DynMapGetElementLeft(b);
return DynMapCalcMapX(b, c)
}
function DynMapPixelToMapY(c, a) {
var b = a - DynMapGetElementTop(c);
return DynMapCalcMapY(c, b)
}
function DynMapCalcPixelX(b, a) {
return b.getPixelFromLonLat(new OpenLayers.LonLat(a * b.precision, 0)).x
}
function DynMapCalcMapX(b, a) {
return b.getLonLatFromPixel(new OpenLayers.Pixel(a, 0)).lon / b.precision
}
function DynMapCalcPixelY(a, b) {
return a.getPixelFromLonLat(new OpenLayers.LonLat(0, b * a.precision)).y
}
function DynMapCalcMapY(b, a) {
return b.getLonLatFromPixel(new OpenLayers.Pixel(0, a)).lat / b.precision
}
function DynMapGetDistanceX(a, b) {
return b * a.getResolution()
}
function DynMapGetDistanceY(b, a) {
return a * b.getResolution()
}
function DynMapSetUserId(b, a) {
}
function DynMapGetUserId(b, a) {
}
function DynMapAnimateZoom(b, c, a) {
if (b && b.zoomTween && b.zoomTween.playing) {
return
}
b.animateZoom(c, a)
}
function DynMapGetCenterX(a) {
return a.getCachedCenter().lon
}
function DynMapGetCenterY(a) {
return a.getCachedCenter().lat
}
function DynMapGetPrecision(a) {
return a.precision
}
function DynMapGetScale(a) {
return a.getLogicalScale()
}
function DynMapAnimate(a, b) {
}
function DynMapEnsureVisible(a, h, f, b, l, e, c) {
var k = DynMapCalcPixelX(a, h);
var j = DynMapCalcPixelY(a, f);
var n = 0,
m = 0;
if (!e) {
e = 50
}
if (k < 0) {
n = - k + e
}
if ((k + b) > a.size.w) {
n = a.size.w - k - b - e
}
if (j < 0) {
m = - j + e
}
if ((j + l) > a.size.h) {
m = a.size.h - j - l - e
}
var i = Math.sqrt(n * n + m * m);
if (i === 0) {
return
}
var g = new OpenLayers.LonLat(h * a.precision, f * a.precision);
if (i < 2 * a.size.w && !c) {
a.pan( - n, - m)
} else {
a.setCenter(g)
}
}
function DynMapClear(c, b, a) {
}
function DynMapReInitLayers(a) {
}
function DynMapAvoidRefreshOnEndScroll(b, a) {
}
function DynMapSetAjaxProxyUrl(b, a) {
}
function DynMapToJSON(b, a) {
return b.toJSON(a)
}
function DynMapGetLayersJson(c, a, b) {
}
function DynMapAddVectorLayer(c, a) {
var b = new OpenLayers.Layer.Vector(a);
c.addLayer(b)
}
function DynMapLayerGetZindex(a) {
return parseInt(a.getZIndex(), 10)
}
function DynMapLayerSetZindex(b, a) {
b.setZIndex(a)
}
function DynMapLayerGetElement(b, a) {
return b.features[a]
}
function DynMapLayerGetNumberElements(a) {
return a.features.length
}
function DynMapLayerDeleteAllElements(a) {
a.removeAllFeatures()
}
function DynMapLayerDeleteElement(c, a) {
var b = c.features[a];
c.removeFeatures([b])
}
function DynMapDeleteVectorElement(a, b) {
return a.removeFeatures([a.getFeatureByFid(b)])
}
function DynMapGetVectorElement(a, b) {
return a.getFeatureByFid(b)
}
function DynMapLayerCreateLine(e, l, j, h, b, c, f) {
var k = [
];
for (var d = 0, g = l.length; d < g; d++) {
k.push(new OpenLayers.Geometry.Point(l[d], j[d]))
}
var a = {
strokeColor: b,
strokeWidth: h,
strokeOpacity: c
};
var m = new OpenLayers.Feature.Vector(new OpenLayers.Geometry.LineString(k), null, a);
if (f) {
m.fid = f
}
m.kind = 'line';
return m
}
function DynMapCreateMultiLine(c, b, d, h, a) {
var f = {
strokeColor: d,
strokeWidth: b,
strokeOpacity: h
};
var g = [
];
for (var e = 0; e < c.length; e++) {
g.push(c[e].geometry)
}
return new OpenLayers.Feature.Vector(new OpenLayers.Geometry.MultiLineString(g), null, f)
}
function DynMapLayerCreatePolygon(j, o, n, c, l, d, e, g, b) {
var a = {
fillColor: c,
strokeColor: d,
strokeOpacity: e,
strokeWidth: l,
fillOpacity: g ? 0 : e
};
var m = [
];
for (var f = 0, k = o.length;
f < k; f++) {
m.push(new OpenLayers.Geometry.Point(o[f], n[f]))
}
var h = new OpenLayers.Geometry.LinearRing(m);
var p = new OpenLayers.Feature.Vector(new OpenLayers.Geometry.Polygon(h), null, a);
if (b) {
p.fid = b
}
p.kind = 'poly';
return p
}
function DynMapLayerCreateCircle(f, l, k, h, i, c, b, m, d, e, g, o, j) {
var a = {
fillColor: m,
strokeColor: e,
strokeOpacity: g,
strokeWidth: d,
fillOpacity: o ? g : 0
};
var n = new OpenLayers.Feature.Vector(OpenLayers.Geometry.Polygon.createRegularPolygon(new OpenLayers.Geometry.Point(l, k), h, 50, 0), null, a);
if (j) {
n.fid = j
}
n.kind = 'circle';
return n
}
function DynMapLayerCreateRectangle(f, h, g, b, k, c, j, d, e, m, i) {
var a = {
fillColor: c,
strokeColor: d,
strokeOpacity: e,
strokeWidth: j,
fillOpacity: m ? e : 0
};
var l = new OpenLayers.Feature.Vector(new OpenLayers.Bounds(h, g, h + b, g + k).toGeometry(), null, a);
if (i) {
l.fid = i
}
l.kind = 'rect';
return l
}
function DynMapLayerAddElement(d, e, f) {
if (!f) {
var b = e.geometry.getVertices();
for (var c = 0, a = b.length; c < a; c++) {
b[c].x *= d.map.precision;
b[c].y *= d.map.precision
}
}
return d.addFeatures([e])
}
function DynMapElementGetPointsX(a, b) {
return GCUI.Util.getFeatureXpoints(a, b)
}
function DynMapElementGetPointsY(a, b) {
return GCUI.Util.getFeatureYpoints(a, b)
}
function DynMapElementGetBounds(d, e) {
var a = d.geometry.getBounds();
var c = e ? 1 : d.layer.map.precision;
return {
mapx1: a.left / c,
mapx2: a.right / c,
mapy1: a.bottom / c,
mapy2: a.top / c
}
}
function DynMapElementGetId(a) {
return a.fid
}
function DynMapElementGetKind(a) {
return a.kind
}
function DynMapElementGetWkt(b, a) {
return GCUI.Util.getFeatureWkt(b, a)
}
function DynMapElementSetWkt(c, a, b) {
c.geometry = (new OpenLayers.Format.WKT()).read(a).geometry
}
function DynMapElementSetWidth(b, a) {
}
function DynMapElementSetOpacity(a, b) {
if (b > 1) {
b = 1
}
if (b < 0) {
b = 0
}
a.style.strokeOpacity = b;
a.style.fillOpacity = b
}
function DynMapElementGetOpacity(a) {
return a.style.fillOpacity
}
function DynMapElementContainsPoint(e, k, j, a) {
var b = a.precision;
var h = DynMapGetDistanceX(a, e.style.strokeWidth);
var l = [
];
l.push(new OpenLayers.Geometry.Point(k * b - h, j * b - h));
l.push(new OpenLayers.Geometry.Point(k * b + h, j * b - h));
l.push(new OpenLayers.Geometry.Point(k * b + h, j * b + h));
l.push(new OpenLayers.Geometry.Point(k * b - h, j * b + h));
var f = new OpenLayers.Geometry.LinearRing(l);
if (e.geometry instanceof OpenLayers.Geometry.MultiPolygon) {
var c = false;
for (var d = 0, g = e.geometry.components.length; d < g; ++d) {
c = e.geometry.components[d].intersects(f);
if (c) {
break
}
}
return c
}
return e.geometry.intersects(f)
}
function DynMapElementToJSON(c, b, a) {
return GCUI.Util.getFeatureJSON(c, b, a)
}
function DynMapElementDeleteLastPoint(c) {
var a = c.geometry.getVertices();
var b = a[a.length - 1];
if (c.geometry.CLASS_NAME === 'OpenLayers.Geometry.LineString') {
c.geometry.removePoint(b)
} else {
if (c.geometry.CLASS_NAME === 'OpenLayers.Geometry.Polygon') {
c.geometry.components[0].removePoint(b)
}
}
}
function DynMapElementGetRadius(c, d) {
var b = c.geometry.getBounds();
var a = d ? 1 : c.layer.map.precision;
return b.getWidth() / (2 * a)
}
function DynMapCircleSetCenter(b, a, d, c) {
if (!c) {
a *= b.layer.map.precision;
d *= b.layer.map.precision
}
b.move(new OpenLayers.LonLat(a, d))
}
function DynMapCircleSetRadius(b, a, d) {
if (!d) {
a *= b.layer.map.precision
}
var c = (DynMapElementGetRadius(b, true) + (a - DynMapElementGetRadius(b, true))) / DynMapElementGetRadius(b, true);
b.geometry.resize(c, new OpenLayers.Geometry.Point(DynMapElementGetPointsX(b, true) [0], DynMapElementGetPointsY(b, true) [0]), 1);
b.layer.redraw()
}
function DynMapLayerZoomOnElement(a, b) {
DynMapLayerZoomOnElements(a, [
b
])
}
function DynMapLayerZoomOnElements(g, f) {
if (!f) {
f = g.features
}
var b = f.length;
var e = 0;
var a;
var j = null;
var h = null;
var d = null;
var c = null;
for (e = 0; e < b; e++) {
a = DynMapElementGetBounds(f[e]);
if (!j || j > a.mapx1) {
j = a.mapx1
}
if (!h || h < a.mapx2) {
h = a.mapx2
}
if (!d || d > a.mapy1) {
d = a.mapy1
}
if (!c || c < a.mapy2) {
c = a.mapy2
}
}
if (g.map && b > 0) {
DynMapCenterOnRect(g.map, j, d, h, c)
}
}
function DynMapLineSwapPoints(j, c, a, g, e, h) {
var f = [
];
for (var b = 0, d = g.length; b < d; b++) {
f.push(new OpenLayers.Geometry.Point(g[b], e[b]))
}
j.simplifiedGeometry = new OpenLayers.Geometry.LineString(f);
j.simplifiedMinScale = c;
j.simplifiedMaxScale = a;
j.simplifiedInMeter = h
}
function DynMapLayerGetSelectedFeatures(a) {
return a.selectedFeatures
}
function DynMapDistanceTo(a, d, c, b) {
return d.geometry.distanceTo(new OpenLayers.Geometry.Point(a.x, a.y), {
edge: b,
details: true
})
}
function DynMapAddDrawingListener(c, a, b) {
if (!c.drawingListeners) {
c.drawingListeners = [
]
}
c.drawingListeners[a] = {
};
c.drawingListeners[a].obj = b
}
function DynMapAddScaleEventListener(c, b, a) {
if (!c.scaleListeners) {
c.scaleListeners = [
]
}
c.scaleListeners[b] = {
};
c.scaleListeners[b].obj = a;
var d = OpenLayers.Function.bind(function (e) {
this.onScaleChange((e.object.numZoomLevels - e.object.zoom))
}, a);
c.events.register('zoomend', a, d);
c.scaleListeners[b].func = d
}
function DynMapAddMoveEventListener(d, a, c) {
if (!d.moveListeners) {
d.moveListeners = [
]
}
d.moveListeners[a] = {
};
d.moveListeners[a].obj = c;
var e = OpenLayers.Function.bind(function (h) {
var g = parseInt(h.object.layerContainerDiv.style.left) - this.startx;
var f = parseInt(h.object.layerContainerDiv.style.top) - this.starty;
this.onMoveChange(g, f)
}, c);
var b = OpenLayers.Function.bind(function (f) {
this.startx = parseInt(f.object.layerContainerDiv.style.left);
this.starty = parseInt(f.object.layerContainerDiv.style.top)
}, c);
d.events.register('move', c, e);
d.events.register('movestart', c, b);
d.moveListeners[a].func = e;
d.moveListeners[a].func1 = b
}
function DynMapAddEndMoveEventListener(c, a, b) {
if (!c.endMoveListeners) {
c.endMoveListeners = [
]
}
c.endMoveListeners[a] = {
};
c.endMoveListeners[a].obj = b;
var d = OpenLayers.Function.bind(function (e) {
this.onEndMove()
}, b);
c.events.register('moveend', b, d);
c.endMoveListeners[a].func = d
}
function DynMapAddObjectEventListener(c, a, b) {
if (!c.objectEventListeners) {
c.objectEventListeners = [
]
}
b.name = a;
c.objectEventListeners.push(b)
}
function DynMapAddMouseSelectionEventListener(f, c, d) {
if (!f.mouseSelectionEventListeners) {
f.mouseSelectionEventListeners = [
]
}
f.mouseSelectionEventListeners[c] = {
};
f.mouseSelectionEventListeners[c].obj = d;
var g = OpenLayers.Function.bind(function (h) {
var i = f.events.getMousePosition(h);
this.evt = h;
if (this.downXY && this.downXY.x === i.x && this.downXY.y === i.y) {
this.onSelect(DynMapCalcMapX(f, i.x), DynMapCalcMapY(f, i.y), i.x, i.y, false)
}
}, d);
var e = OpenLayers.Function.bind(function (h) {
var i = f.events.getMousePosition(h);
this.onSelect(DynMapCalcMapX(f, i.x), DynMapCalcMapY(f, i.y), i.x, i.y, true)
}, d);
var a = OpenLayers.Function.bind(function (h) {
this.downXY = f.events.getMousePosition(h)
}, d);
f.events.registerPriority('mousedown', d, a);
f.events.register('mouseup', d, g);
var b = new GCUI.Control.RightClick({
onRightClick: e,
autoActivate: true
});
f.addControl(b);
f.mouseSelectionEventListeners[c].func = g;
f.mouseSelectionEventListeners[c].rightControl = b;
f.mouseSelectionEventListeners[c].downFunc = a
}
function DynMapRemoveListener(g, e, a) {
var f = null;
if (e === 'scale' && g.scaleListeners) {
e = 'zoomend';
f = g.scaleListeners[a]
}
if (e === 'move' && g.moveListeners) {
f = g.moveListeners[a];
g.events.unregister('movestart', f.obj, f.func1)
}
if (e === 'endMove' && g.endMoveListeners) {
e = 'moveend';
f = g.endMoveListeners[a]
}
if (e === 'click' && g.mouseSelectionEventListeners) {
e = 'mouseup';
f = g.mouseSelectionEventListeners[a];
g.events.unregister('mousedown', f.obj, f.downFunc);
f.rightControl.destroy()
}
if (e === 'draw' && g.drawingListeners) {
delete g.drawingListeners[a]
}
if (f) {
g.events.unregister(e, f.obj, f.func)
}
if (e === 'objects' && g.objectEventListeners) {
var d = [
];
var c = g.objectEventListeners.length;
for (var b = 0; b < c; b++) {
if (g.objectEventListeners[b].name !== a) {
d.push(g.objectEventListeners[b])
}
}
g.objectEventListeners = d
}
}
function DynMapCreateMode(e, c, d, a, b) {
return DynMapCreateSelectionMode(e, c, d, a, b)
}
function DynMapCreateSelectionMode(d, i, l, e, j, g, f) {
var k;
var a = false;
var c = 0;
var b;
if (d === 'line') {
k = OpenLayers.Handler.Path;
b = {
strokeColor: e,
strokeWidth: l,
strokeOpacity: j
}
} else {
if (d === 'circle') {
c = 40;
k = OpenLayers.Handler.RegularPolygon
} else {
if (d === 'rect') {
a = true;
c = 4;
k = OpenLayers.Handler.RegularPolygon
} else {
k = OpenLayers.Handler.Polygon
}
}
if (g !== null) {
b = {
fillColor: e,
strokeWidth: l,
fillOpacity: j,
strokeOpacity: j,
strokeColor: g
}
} else {
b = {
fillColor: e,
strokeWidth: l,
strokeOpacity: j,
fillOpacity: j
}
}
}
var h = {
name: 'createMode',
handlerOptions: {
layerStyle: b,
handlerType: k,
layerName: i,
sides: c,
irregular: a
},
kind: d
};
return h
}
function DynMapDistanceMode(f, i, d, g, b, c, h) {
var a = {
strokeColor: d,
strokeWidth: i,
strokeOpacity: g
};
var e = {
name: 'distanceMode',
handlerOptions: {
layerName: f,
divseg: b,
divtot: c,
distlistener: h,
layerStyle: a
}
};
return e
}
function DynMapMoveMode() {
return {
name: 'moveMode'
}
}
function DynMapMoveSelectionMode() {
return {
name: 'moveMode'
}
}
function DynMapCreateUnZoomMode() {
return {
name: 'unzoom'
}
}
function DynMapAddRasterLayer(s, v, j, e, o, m, r, i, q, l, g, c) {
var b = s.baseLayer;
var t = l ? l : 300;
var d = g ? g : 300;
var n = s.options;
var p = b ? b.url : n.server;
var h = b ? b.mapname : n.mapName;
var f = b ? b.precision : n.precision;
var k = b ? b.ratios : n.ratios.split('~');
var a = b ? b.extent : (n.limits ? {
minX: n.limits[0],
maxX: n.limits[1],
minY: n.limits[2],
maxY: n.limits[3]
}
 : null);
var u = new GCUI.Layer.GeoConcept(v, (j && j !== '') ? j : p, {
mapname: (e && e !== '') ? e : h,
tabname: o
}, {
extension: m,
mapversion: (c ? c : 0),
tileSize: new OpenLayers.Size(t, d),
transparent: r,
precision: f,
ratios: k,
extent: a,
isBaseLayer: b ? false : true
});
s.addLayer(u)
}
function DynMapAddDynamicLayer(a, c, b, i, g, k, j, l, d, e) {
var h = a.baseLayer;
var f = new GCUI.Layer.GeoConcept(c, h.url, {
mapname: h.mapname,
tabname: null
}, {
transparent: (b === - 1),
singleTile: true,
urlParams: l,
isBaseLayer: false,
userId: g
});
a.addLayer(f)
}
function DynMapRemoveRasterLayer(c, a) {
var b = c.getLayersByName(a) [0];
if (b) {
c.removeLayer(b)
}
}
function DynMapLayerRefresh(a) {
a.redraw()
}
function DynMapLayerSetTabName(a, b) {
a.tabname = b
}
function DynMapLayerSetOptions(b, a) {
b.urlParams = a
}
function DynMapSetLayerVisibility(d, a, c) {
var b = DynMapGetLayer(d, a);
b.setVisibility(c)
}
function DynMapGetLayerVisibility(a) {
return a.getVisibility()
}
function DynMapGetRasterLayerNames(d) {
var c = d.layers;
var b = [
];
for (var a = 0; a < c.length; a++) {
if (c[a].CLASS_NAME === 'GCUI.Layer.GeoConcept') {
b.push(c[a].name)
}
}
return b
}
function DynMapGetLayer(b, a) {
return b.getLayersByName(a) [0] || null
}
function DynMapSetLayerCopyright(a, e, d, c, b) {
a.attribution = (d ? '<img src=\'' + d + '\'/>' : '') + e;
if (a.map) {
a.map.events.triggerEvent('changelayer', {
layer: a,
property: 'attribution'
})
}
}
function DynMapSetLayerOpacity(b, a) {
if (!b) {
return
}
if (b.opacity === 0 && a !== 0 && !b.getVisibility()) {
b.setVisibility(true)
}
if (a === 0) {
b.setVisibility(false)
}
b.setOpacity(a / 100)
}
function DynMapGetLayerOpacity(a) {
return (a.opacity ? a.opacity * 100 : 100)
}
function DynMapSetLayerNumber(c, a, b) {
c.setLayerIndex(DynMapGetLayer(c, a), b)
}
function DynMapGetLayerNumber(b, a) {
return b.getLayerIndex(DynMapGetLayer(b, a))
}
function DynMapAddCopyrightImage(b, a) {
}
function DynMapRemoveCopyrightImage(b, a) {
}
function DynMapAddGlobalView(b, j, h, c, l, d, e, k, a, g, i, f) {
var m = {
posx: j,
posy: h,
width: c,
height: l,
mapName: d,
tabName: e,
format: k,
div: a,
tabgrad: g,
tabscales: i,
isFixed: f
};
b.globalView = new GCUI.Control.GlobalView(m);
b.addControl(b.globalView)
}
function DynMapGetGlobalView(a) {
return a.getControlsByClass('GCUI.Control.GlobalView') [0]
}
function DynMapAddCopyrightLayer(c, a, d) {
if (c.getControlsByClass('OpenLayers.Control.Attribution').length === 0) {
var b = new OpenLayers.Control.Attribution();
c.addControl(b);
if (a < 0) {
b.div.style.right = - a + 'px'
} else {
b.div.style.left = a + 'px'
}
if (d < 0) {
b.div.style.bottom = - d + 'px'
} else {
b.div.style.top = d + 'px'
}
}
}
function DynMapAddScaleLayer(d, a, f, e, c) {
var b = {
posx: a,
posy: f,
barAreaWidth: c,
div: e
};
d.addControl(new GCUI.Control.GraphicScale(b))
}
function DynMapAddSliderLayer(d, c, a, e) {
var b = new GCUI.Control.ScaleSlider({
inverseDir: c,
orientation: a,
div: e,
autoActivate: true
});
b.setMaximumScale(d.maxLogicalScale);
b.setMinimumScale(d.minLogicalScale);
d.addControl(b)
}
function DynMapSetGlobalViewRectSize(c, b, a) {
c.setRectSize(b, a)
}
function DynMapSetAutoZoomReduction(b, a) {
b.setAutoZoomReduction(a)
}
function DynMapSetGlobalViewTab(a, b) {
a.tabName = b
}
function DynMapSetGlobalViewCrossProperties(d, c, b, a) {
d.crossWidth = c;
d.crossHeight = b;
d.crossLineWidth = a;
d.updateCrossDiv()
}
function DynMapGlobalViewAnimateView(a) {
}
function DynMapActivateSelectFeature(d, b, a) {
var c = new OpenLayers.Control.SelectFeature(b, a);
d.addControl(c);
c.activate();
return c
}
function DynMapRemoveControl(a) {
if (a) {
a.destroy()
}
}
function DynMapCreateProjection(b, a) {
return new OpenLayers.Projection(b, a)
}
function DynMapSetProjection(b, a) {
if (typeof a === 'string') {
b.projection = a
}
if (a.getCode) {
b.projection = a.getCode()
}
}
function DynMapGetProjection(a) {
return new OpenLayers.Projection(a.projection)
}
function DynMapGetLonLatFromPixel(f, c, b) {
var e = c - DynMapGetElementLeft(f);
var a = b - DynMapGetElementTop(f);
var d = f.getLonLatFromPixel(new OpenLayers.Pixel(e, a));
d.transform(DynMapGetProjection(f), DynMapCreateProjection('EPSG:4326'));
return {
x: d.lon,
y: d.lat
}
}
function DynMapFormatDegrees(c, d) {
var f = Math.floor(Math.abs(c));
var b = Math.floor(60 * (Math.abs(c) - f));
var e = Math.round(60 * (60 * (Math.abs(c) - f) - b));
if (e === 60) {
e = 0;
b = b + 1
}
if (b === 60) {
b = 0;
f = f + 1
}
var a = (c > 0 ? d[0] : d[1]);
return f + '&deg; ' + b + '\' ' + e + '\'\' ' + a
}
function DynMapDMStoDegrees(e, c, f, d, a) {
var b = e + c / 60 + f / 3600;
if (d === a[0] || d === a[1]) {
b = - b
}
return b
}
function DynMapBrowserGetXposition(b, a) {
return a.xy.x + DynMapGetElementLeft(b)
}
function DynMapBrowserGetYposition(b, a) {
return a.xy.y + DynMapGetElementTop(b)
}
function DynMapGetElementLeft(a) {
return OpenLayers.Util.pagePosition(a.div) [0]
}
function DynMapGetElementTop(a) {
return OpenLayers.Util.pagePosition(a.div) [1]
}
function DynMapCreateObject(j, h, b, a, k, i, g, d, c, f, e) {
return {
id: b,
mapx: j,
mapy: h,
name: a,
text: k,
deltaX: - i,
deltaY: - g,
imgsrc: d,
innerHTML: c,
type: f,
objnamecss: e
}
}
function dynMapCreateObjectStyle(e, a, j, b, k, i, h, l, f, g, d, c) {
return new GCISObjectStyle(e, a, j, b, k, i, h, l, f, g, d, c)
}
function dynMapCreateObjectType(a, b) {
return new GCISObjectType(a, b)
}
function DynMapSetObjectNameBackgroundColor(b, a) {
b.nameBackgroundColor = a;
if (b.nameDiv) {
b.nameDiv.style.backgroundColor = a
}
}
function DynMapSetObjectVisibilityRange(c, a, b) {
c.visMinScale = a;
c.visMaxScale = b
}
function DynMapSetObjectNameCss(b, c, a) {
return b.objectLayer.setObjectNameCss(c, a)
}
function DynMapSetObjectDivCss(b, c, a) {
return b.objectLayer.setObjectDivCss(c, a)
}
function DynMapGetNumObject(a, b) {
return a.objectLayer.getNumObject(b)
}
function DynMapGetObjectXY(a, c, b) {
return a.objectLayer.getObjectXY(c, b)
}
function DynMapGetObjectDiv(a) {
return a.mainDiv
}
function DynMapHideObjectSheet(c) {
var a = c.getControlsByClass('GCUI.Control.Popup');
for (var b = 0; b < a.length; b++) {
a[b].destroy()
}
}
function DynMapGetObjects(a) {
return a.objectLayer.objects
}
function DynMapGetObject(b, a) {
return b.objectLayer.findObject(a)
}
function DynMapActivateObjectMultiLabels(a) {
a.objectLayer.activateMultiLabels()
}
function DynMapSetObjectSheetVisibility(c, a, b) {
c.objectLayer.setNameDivVisibilityRange(a, b)
}
function DynMapShowObjectSheet(f, c) {
DynMapHideObjectSheet(f);
if (!c) {
return
}
var e = f.objectLayer.findObject(c);
if (!e) {
return
}
DynMapEnsureVisible(f, e.mapx, e.mapy, f.sheetMargin || 110, f.sheetMargin || 50);
if (e.text) {
var d = f.precision;
var a = new GCUI.Control.Popup(new OpenLayers.LonLat(e.mapx * d, e.mapy * d), e.text, null);
f.addControl(a)
}
if (f.objectEventListeners) {
var b,
g;
for (b = 0; b < f.objectEventListeners.length; b++) {
g = f.objectEventListeners[b];
if (g.onObjectSheetChange) {
g.onObjectSheetChange(c, e)
}
if (g.onObjectClick) {
g.onObjectClick(c, e)
}
}
}
}
function DynMapSetObjects(a, d, c, b) {
a.objectLayer.clearObjects();
a.objectLayer.addObjects(d, c);
if (!b) {
a.objectLayer.refresh()
}
}
function DynMapMoveObjects(a, c, b) {
}
function DynMapAddObject(b, a, c) {
b.objectLayer.addObject(a, c)
}
function DynMapRemoveObject(b, a) {
b.objectLayer.removeObject(a)
}
function DynMapRemoveAllObjects(a) {
a.objectLayer.clearObjects()
}
function DynMapCenterOnObjects(b) {
var a = b.getLayersByName('objects');
if (a[0] && a[0].getDataExtent()) {
b.zoomToExtent(a[0].getDataExtent())
}
}
function DynMapCenterOnObject(f, c, a) {
var b = f.getLayersByName('objects');
if (b[0]) {
var e = b[0].findObject(c);
if (!e) {
return
}
var d = new OpenLayers.LonLat(e.mapx * f.precision, e.mapy * f.precision);
if (a) {
f.panTo(d)
} else {
f.moveTo(d)
}
}
}
function DynMapSetObjectDragMode(b, a) {
b.objectLayer.setDragMode(a)
}
function SliderSetMinimum(b, a) {
b.setMinimum(a)
}
function SliderSetMaximum(b, a) {
b.setMaximum(a)
}
function SliderSetOnRelease(b, a) {
b.onrelease = a
}
function SliderSetDrawSpans(b, a) {
b.drawSpans = a
}
function SliderGetValue(a) {
return a.getValue()
}
function SliderSetValue(a, b) {
return a.setValue(b)
}
OpenLayers.TileManager.prototype.addLayer = function (a) {
var d = a.layer;
if (d instanceof OpenLayers.Layer.Grid && !a.layer.singleTile) {
d.events.on({
addtile: this.addTile,
retile: this.clearTileQueue,
scope: this
});
var c,
b,
e;
for (c = d.grid.length - 1; c >= 0; --c) {
for (b = d.grid[c].length - 1; b >= 0; --b) {
e = d.grid[c][b];
this.addTile({
tile: e
});
if (e.url && !e.imgDiv) {
this.manageTileCache({
object: e
})
}
}
}
}
};
OpenLayers.Layer.Vector.prototype.drawFeature = function (d, e) {
if (!this.drawn) {
return
}
if (typeof e != 'object') {
if (!e && d.state === OpenLayers.State.DELETE) {
e = 'delete'
}
var f = e || d.renderIntent;
e = d.style || this.style;
if (!e) {
e = this.styleMap.createSymbolizer(d, f)
}
}
if (this.map && d.simplifiedGeometry) {
var g = this.map.getLogicalScale();
if ((d.simplifiedMinScale <= g) && (d.simplifiedMaxScale >= g)) {
if (!d.noSimplifiedGeometry) {
d.noSimplifiedGeometry = d.geometry.clone();
if (!d.simplifiedInMeter) {
var b = d.simplifiedGeometry.getVertices();
for (var c = 0, a = b.length; c < a; c++) {
b[c].x *= this.map.precision;
b[c].y *= this.map.precision
}
}
}
d.geometry.components = d.simplifiedGeometry.clone().components
} else {
if (d.noSimplifiedGeometry) {
d.geometry.components = d.noSimplifiedGeometry.clone().components
}
}
}
var h = this.renderer.drawFeature(d, e);
if (h === false || h === null) {
this.unrenderedFeatures[d.id] = d
} else {
delete this.unrenderedFeatures[d.id]
}
};
OpenLayers.Rule.prototype.evaluate = function (c) {
var b = this.getContext(c);
var a = true;
if (this.minScaleDenominator || this.maxScaleDenominator) {
var d = c.layer.map.getLogicalScale()
}
if (this.minScaleDenominator) {
a = d >= OpenLayers.Style.createLiteral(this.minScaleDenominator, b)
}
if (a && this.maxScaleDenominator) {
a = d <= OpenLayers.Style.createLiteral(this.maxScaleDenominator, b)
}
if (a && this.filter) {
if (this.filter.CLASS_NAME == 'OpenLayers.Filter.FeatureId') {
a = this.filter.evaluate(c)
} else {
a = this.filter.evaluate(b)
}
}
return a
};
