/*
 * JavaScript Load Image 1.1.4
 * https://github.com/blueimp/JavaScript-Load-Image
 *
 * Copyright 2011, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

/*jslint nomen: true */
/*global window, document, URL, webkitURL, Blob, File, FileReader, define */

(function ($) {
    'use strict';

    // Loads an image for a given File object.
    // Invokes the callback with an img or optional canvas
    // element (if supported by the browser) as parameter:
    var loadImage = function (file, callback, options) {
            var img = document.createElement('img'),
                url,
                oUrl;
            img.onerror = callback;
            img.onload = function () {
                if (oUrl) {
                    loadImage.revokeObjectURL(oUrl);
                }
                callback(loadImage.scale(img, options));
            };
            if ((window.Blob && file instanceof Blob) ||
                // Files are also Blob instances, but some browsers
                // (Firefox 3.6) support the File API but not Blobs:
                    (window.File && file instanceof File)) {
                url = oUrl = loadImage.createObjectURL(file);
            } else {
                url = file;
            }
            if (url) {
                img.src = url;
                return img;
            } else {
                return loadImage.readFile(file, function (url) {
                    img.src = url;
                });
            }
        },
        urlAPI = (window.createObjectURL && window) ||
            (window.URL && URL) || (window.webkitURL && webkitURL);

    // Scales the given image (img HTML element)
    // using the given options.
    // Returns a canvas object if the canvas option is true
    // and the browser supports canvas, else the scaled image:
    loadImage.scale = function (img, options) {
        options = options || {};
        var canvas = document.createElement('canvas'),
            scale = Math.max(
                (options.minWidth || img.width) / img.width,
                (options.minHeight || img.height) / img.height
            );
        if (scale > 1) {
            img.width = parseInt(img.width * scale, 10);
            img.height = parseInt(img.height * scale, 10);
        }
        scale = Math.min(
            (options.maxWidth || img.width) / img.width,
            (options.maxHeight || img.height) / img.height
        );
        if (scale < 1) {
            img.width = parseInt(img.width * scale, 10);
            img.height = parseInt(img.height * scale, 10);
        }
        if (!options.canvas || !canvas.getContext) {
            return img;
        }
        canvas.width = img.width;
        canvas.height = img.height;
        canvas.getContext('2d')
            .drawImage(img, 0, 0, img.width, img.height);
        return canvas;
    };

    loadImage.createObjectURL = function (file) {
        return urlAPI ? urlAPI.createObjectURL(file) : false;
    };

    loadImage.revokeObjectURL = function (url) {
        return urlAPI ? urlAPI.revokeObjectURL(url) : false;
    };

    // Loads a given File object via FileReader interface,
    // invokes the callback with a data url:
    loadImage.readFile = function (file, callback) {
        if (window.FileReader && FileReader.prototype.readAsDataURL) {
            var fileReader = new FileReader();
            fileReader.onload = function (e) {
                callback(e.target.result);
            };
            fileReader.readAsDataURL(file);
            return fileReader;
        }
        return false;
    };

    if (typeof define !== 'undefined' && define.amd) {
        define(function () {
            return loadImage;
        });
    } else {
        $.loadImage = loadImage;
    }
}(this));
