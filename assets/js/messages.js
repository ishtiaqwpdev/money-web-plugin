/**
 * Gospel Music Mastery — frontend messaging (localStorage demo)
 * Shared by teacher, student, and admin message pages.
 */
(function (global) {
  'use strict';

  var STORAGE_KEY = 'gmm_messages_v1';

  var SEED = [
    {
      id: 'john-smith__sarah-johnson',
      teacherId: 'john-smith',
      teacherName: 'John Smith',
      teacherAvatar: 'assets/img/team/01.jpg',
      studentId: 'sarah-johnson',
      studentName: 'Sarah Johnson',
      studentAvatar: 'assets/img/team/02.jpg',
      messages: [
        { id: 'm1', from: 'student', text: 'Hi John! Can we move Thursday’s piano lesson to Friday?', at: '2026-03-18T10:12:00.000Z' },
        { id: 'm2', from: 'teacher', text: 'Yes — Friday at 10:00 AM works for me. I’ll update the booking.', at: '2026-03-18T10:20:00.000Z' },
        { id: 'm3', from: 'student', text: 'Perfect, thank you!', at: '2026-03-18T10:22:00.000Z' }
      ]
    },
    {
      id: 'emily-davis__sarah-johnson',
      teacherId: 'emily-davis',
      teacherName: 'Emily Davis',
      teacherAvatar: 'assets/img/team/02.jpg',
      studentId: 'sarah-johnson',
      studentName: 'Sarah Johnson',
      studentAvatar: 'assets/img/team/02.jpg',
      messages: [
        { id: 'm1', from: 'teacher', text: 'Great progress on your vocal warm-ups this week.', at: '2026-03-17T15:00:00.000Z' },
        { id: 'm2', from: 'student', text: 'Thanks Emily! I’ll practice the hymn arrangement before Sunday.', at: '2026-03-17T15:08:00.000Z' }
      ]
    },
    {
      id: 'john-smith__ahmed-clayton',
      teacherId: 'john-smith',
      teacherName: 'John Smith',
      teacherAvatar: 'assets/img/team/01.jpg',
      studentId: 'ahmed-clayton',
      studentName: 'Ahmed Clayton',
      studentAvatar: 'assets/img/team/03.jpg',
      messages: [
        { id: 'm1', from: 'student', text: 'Hello — I’d like to book a beginner gospel piano lesson.', at: '2026-03-16T09:00:00.000Z' },
        { id: 'm2', from: 'teacher', text: 'Welcome Ahmed! I have openings this week. What days work for you?', at: '2026-03-16T09:15:00.000Z' }
      ]
    }
  ];

  function slugify(name) {
    return String(name || '')
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-|-$/g, '') || 'user';
  }

  function threadId(teacherId, studentId) {
    return teacherId + '__' + studentId;
  }

  function load() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({ threads: SEED }));
        return { threads: JSON.parse(JSON.stringify(SEED)) };
      }
      var data = JSON.parse(raw);
      if (!data || !Array.isArray(data.threads)) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({ threads: SEED }));
        return { threads: JSON.parse(JSON.stringify(SEED)) };
      }
      return data;
    } catch (e) {
      return { threads: JSON.parse(JSON.stringify(SEED)) };
    }
  }

  function save(data) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    } catch (e) { /* ignore quota */ }
  }

  function getThreads() {
    return load().threads.slice().sort(function (a, b) {
      return lastAt(b) - lastAt(a);
    });
  }

  function lastAt(thread) {
    var msgs = thread.messages || [];
    if (!msgs.length) return 0;
    return new Date(msgs[msgs.length - 1].at).getTime();
  }

  function getThread(id) {
    var data = load();
    for (var i = 0; i < data.threads.length; i++) {
      if (data.threads[i].id === id) return data.threads[i];
    }
    return null;
  }

  function ensureThread(opts) {
    var teacherId = opts.teacherId || slugify(opts.teacherName);
    var studentId = opts.studentId || slugify(opts.studentName);
    var id = threadId(teacherId, studentId);
    var data = load();
    var existing = null;
    for (var i = 0; i < data.threads.length; i++) {
      if (data.threads[i].id === id) {
        existing = data.threads[i];
        break;
      }
    }
    if (existing) return existing;

    var thread = {
      id: id,
      teacherId: teacherId,
      teacherName: opts.teacherName || 'Teacher',
      teacherAvatar: opts.teacherAvatar || 'assets/img/team/01.jpg',
      studentId: studentId,
      studentName: opts.studentName || 'Student',
      studentAvatar: opts.studentAvatar || 'assets/img/team/02.jpg',
      messages: []
    };
    data.threads.push(thread);
    save(data);
    return thread;
  }

  function sendMessage(threadIdValue, fromRole, text) {
    var clean = String(text || '').trim();
    if (!clean) return null;
    var data = load();
    var thread = null;
    for (var i = 0; i < data.threads.length; i++) {
      if (data.threads[i].id === threadIdValue) {
        thread = data.threads[i];
        break;
      }
    }
    if (!thread) return null;
    var msg = {
      id: 'm' + Date.now(),
      from: fromRole,
      text: clean,
      at: new Date().toISOString()
    };
    thread.messages.push(msg);
    save(data);
    return msg;
  }

  function formatTime(iso) {
    try {
      var d = new Date(iso);
      return d.toLocaleString(undefined, {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
      });
    } catch (e) {
      return '';
    }
  }

  function preview(thread) {
    var msgs = thread.messages || [];
    if (!msgs.length) return 'No messages yet';
    return msgs[msgs.length - 1].text;
  }

  function queryParam(name) {
    try {
      return new URLSearchParams(window.location.search).get(name);
    } catch (e) {
      return null;
    }
  }

  /** Wire any [data-gmm-message] CTAs on the page */
  function wireMessageButtons() {
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-gmm-message]');
      if (!btn) return;
      e.preventDefault();

      var role = btn.getAttribute('data-gmm-message'); // teacher | student
      var teacherName = btn.getAttribute('data-teacher-name') || 'John Smith';
      var teacherId = btn.getAttribute('data-teacher-id') || slugify(teacherName);
      var studentName = btn.getAttribute('data-student-name') || 'Sarah Johnson';
      var studentId = btn.getAttribute('data-student-id') || slugify(studentName);
      var teacherAvatar = btn.getAttribute('data-teacher-avatar') || 'assets/img/team/01.jpg';
      var studentAvatar = btn.getAttribute('data-student-avatar') || 'assets/img/team/02.jpg';

      ensureThread({
        teacherId: teacherId,
        teacherName: teacherName,
        teacherAvatar: teacherAvatar,
        studentId: studentId,
        studentName: studentName,
        studentAvatar: studentAvatar
      });

      var tid = threadId(teacherId, studentId);
      if (role === 'teacher') {
        window.location.href = 'teacher-messages.html?thread=' + encodeURIComponent(tid);
      } else if (role === 'admin') {
        window.location.href = 'admin-messages.html?thread=' + encodeURIComponent(tid);
      } else {
        window.location.href = 'student-messages.html?thread=' + encodeURIComponent(tid);
      }
    });
  }

  /**
   * Mount chat UI into a page.
   * opts: { role: 'teacher'|'student'|'admin', listEl, chatEl, selfName }
   */
  function mountInbox(opts) {
    var role = opts.role;
    var listEl = typeof opts.listEl === 'string' ? document.querySelector(opts.listEl) : opts.listEl;
    var chatEl = typeof opts.chatEl === 'string' ? document.querySelector(opts.chatEl) : opts.chatEl;
    if (!listEl || !chatEl) return;

    var activeId = queryParam('thread') || '';
    var withTeacher = queryParam('teacher');
    var withStudent = queryParam('student');

    if (!activeId && withTeacher && role === 'student') {
      var t = ensureThread({
        teacherId: slugify(withTeacher),
        teacherName: withTeacher.replace(/-/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); }),
        studentId: 'sarah-johnson',
        studentName: 'Sarah Johnson'
      });
      activeId = t.id;
    }
    if (!activeId && withStudent && role === 'teacher') {
      var s = ensureThread({
        teacherId: 'john-smith',
        teacherName: 'John Smith',
        studentId: slugify(withStudent),
        studentName: withStudent.replace(/-/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); })
      });
      activeId = s.id;
    }

    function partner(thread) {
      if (role === 'teacher') {
        return { name: thread.studentName, avatar: thread.studentAvatar, label: 'Student' };
      }
      if (role === 'student') {
        return { name: thread.teacherName, avatar: thread.teacherAvatar, label: 'Teacher' };
      }
      return {
        name: thread.teacherName + ' ↔ ' + thread.studentName,
        avatar: thread.teacherAvatar,
        label: 'Conversation'
      };
    }

    function filteredThreads() {
      var all = getThreads();
      if (role === 'teacher') {
        return all.filter(function (t) { return t.teacherId === 'john-smith'; });
      }
      if (role === 'student') {
        return all.filter(function (t) { return t.studentId === 'sarah-johnson'; });
      }
      return all;
    }

    function renderList() {
      var threads = filteredThreads();
      if (!threads.length) {
        listEl.innerHTML = '<p class="gmm-msg-empty">No conversations yet.</p>';
        return;
      }
      listEl.innerHTML = threads.map(function (t) {
        var p = partner(t);
        var isActive = t.id === activeId ? ' is-active' : '';
        return (
          '<button type="button" class="gmm-msg-thread' + isActive + '" data-thread-id="' + t.id + '">' +
            '<img src="' + p.avatar + '" alt="">' +
            '<span class="gmm-msg-thread-body">' +
              '<strong>' + p.name + '</strong>' +
              '<small>' + preview(t) + '</small>' +
            '</span>' +
          '</button>'
        );
      }).join('');
    }

    function renderChat() {
      if (!activeId) {
        chatEl.innerHTML =
          '<div class="gmm-msg-placeholder">' +
            '<i class="far fa-comments"></i>' +
            '<h4>Select a conversation</h4>' +
            '<p>Choose a chat from the list to view messages.</p>' +
          '</div>';
        return;
      }

      var thread = getThread(activeId);
      if (!thread) {
        chatEl.innerHTML = '<div class="gmm-msg-placeholder"><p>Conversation not found.</p></div>';
        return;
      }

      var p = partner(thread);
      var canReply = role !== 'admin';
      var bubbles = (thread.messages || []).map(function (m) {
        var mine =
          (role === 'teacher' && m.from === 'teacher') ||
          (role === 'student' && m.from === 'student');
        var side = role === 'admin'
          ? (m.from === 'teacher' ? ' is-left' : ' is-right')
          : (mine ? ' is-mine' : ' is-theirs');
        var who = m.from === 'teacher' ? thread.teacherName : thread.studentName;
        return (
          '<div class="gmm-msg-bubble' + side + '">' +
            (role === 'admin' ? '<span class="gmm-msg-who">' + who + '</span>' : '') +
            '<p>' + escapeHtml(m.text) + '</p>' +
            '<time>' + formatTime(m.at) + '</time>' +
          '</div>'
        );
      }).join('');

      chatEl.innerHTML =
        '<div class="gmm-msg-chat-head">' +
          '<img src="' + p.avatar + '" alt="">' +
          '<div><strong>' + p.name + '</strong><span>' + p.label + '</span></div>' +
        '</div>' +
        '<div class="gmm-msg-chat-body" id="gmm-msg-scroll">' + (bubbles || '<p class="gmm-msg-empty">No messages yet. Say hello!</p>') + '</div>' +
        (canReply
          ? '<form class="gmm-msg-compose" id="gmm-msg-form">' +
              '<input type="text" id="gmm-msg-input" class="form-control" placeholder="Type a message…" autocomplete="off" required>' +
              '<button type="submit" class="theme-btn"><i class="far fa-paper-plane"></i> Send</button>' +
            '</form>'
          : '<div class="gmm-msg-admin-note"><i class="far fa-eye"></i> Admin view only — you can monitor this chat but not reply.</div>');

      var scroll = document.getElementById('gmm-msg-scroll');
      if (scroll) scroll.scrollTop = scroll.scrollHeight;

      var form = document.getElementById('gmm-msg-form');
      if (form) {
        form.addEventListener('submit', function (ev) {
          ev.preventDefault();
          var input = document.getElementById('gmm-msg-input');
          var text = input ? input.value : '';
          var fromRole = role === 'teacher' ? 'teacher' : 'student';
          if (sendMessage(activeId, fromRole, text)) {
            if (input) input.value = '';
            renderList();
            renderChat();
          }
        });
      }
    }

    function escapeHtml(str) {
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    listEl.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-thread-id]');
      if (!btn) return;
      activeId = btn.getAttribute('data-thread-id');
      if (window.history && window.history.replaceState) {
        var url = new URL(window.location.href);
        url.searchParams.set('thread', activeId);
        window.history.replaceState({}, '', url);
      }
      renderList();
      renderChat();
    });

    var threads = filteredThreads();
    if (!activeId && threads.length) activeId = threads[0].id;

    renderList();
    renderChat();
  }

  // Auto-wire CTAs
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', wireMessageButtons);
  } else {
    wireMessageButtons();
  }

  global.GmmMessages = {
    getThreads: getThreads,
    getThread: getThread,
    ensureThread: ensureThread,
    sendMessage: sendMessage,
    mountInbox: mountInbox,
    slugify: slugify,
    threadId: threadId,
    formatTime: formatTime
  };
})(window);
