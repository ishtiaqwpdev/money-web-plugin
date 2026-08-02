/**
 * Gospel Music Mastery — favourite teachers (frontend demo)
 * Persists in localStorage so teachers listing + favourites page stay in sync.
 */
(function (window) {
  'use strict';

  var KEY = 'gmm_favourite_teachers';

  function normalize(name) {
    return String(name || '').trim().toLowerCase();
  }

  function read() {
    try {
      var raw = window.localStorage.getItem(KEY);
      var list = raw ? JSON.parse(raw) : [];
      return Array.isArray(list) ? list : [];
    } catch (err) {
      return [];
    }
  }

  function write(list) {
    try {
      window.localStorage.setItem(KEY, JSON.stringify(list));
    } catch (err) {
      /* ignore quota / private mode */
    }
  }

  function isFav(name) {
    var needle = normalize(name);
    return read().some(function (item) {
      return normalize(item) === needle;
    });
  }

  function add(name) {
    var display = String(name || '').trim();
    if (!display || isFav(display)) return read();
    var list = read();
    list.push(display);
    write(list);
    return list;
  }

  function remove(name) {
    var needle = normalize(name);
    var list = read().filter(function (item) {
      return normalize(item) !== needle;
    });
    write(list);
    return list;
  }

  function toggle(name) {
    if (isFav(name)) {
      remove(name);
      return false;
    }
    add(name);
    return true;
  }

  function seedIfEmpty(names) {
    if (read().length) return read();
    var unique = [];
    (names || []).forEach(function (name) {
      var display = String(name || '').trim();
      if (!display) return;
      if (unique.some(function (item) { return normalize(item) === normalize(display); })) return;
      unique.push(display);
    });
    write(unique);
    return unique;
  }

  window.GMMFavourites = {
    read: read,
    write: write,
    isFav: isFav,
    add: add,
    remove: remove,
    toggle: toggle,
    seedIfEmpty: seedIfEmpty,
    normalize: normalize
  };
})(window);
