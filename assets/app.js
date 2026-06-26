(function () {
  function qs(selector, root) {
    return (root || document).querySelector(selector);
  }

  function qsa(selector, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(selector));
  }

  function showToast(message) {
    var toast = document.createElement('div');
    toast.className = 'toast success';
    toast.textContent = message;
    toast.style.position = 'fixed';
    toast.style.right = '18px';
    toast.style.bottom = '18px';
    toast.style.zIndex = '140';
    document.body.appendChild(toast);
    window.setTimeout(function () { toast.remove(); }, 2200);
  }

  qsa('[data-copy]').forEach(function (button) {
    button.addEventListener('click', function () {
      var text = button.getAttribute('data-copy') || '';
      navigator.clipboard.writeText(text).then(function () {
        showToast('已复制');
      });
    });
  });

  qsa('[data-drawer-title]').forEach(function (card) {
    card.addEventListener('click', function (event) {
      if (event.target.closest('a, button, form, input, select')) {
        return;
      }
      var drawer = qs('[data-right-drawer]');
      if (!drawer) {
        return;
      }
      qs('[data-drawer-heading]', drawer).textContent = card.getAttribute('data-drawer-title') || '';
      qs('[data-drawer-text]', drawer).textContent = card.getAttribute('data-drawer-body') || '暂无摘要';
      qs('[data-drawer-link]', drawer).setAttribute('href', card.getAttribute('data-drawer-url') || '#');
    });
  });

  var modal = qs('[data-command-modal]');
  var input = qs('[data-command-input]');
  var results = qs('[data-command-results]');

  function openCommand() {
    if (!modal) {
      return;
    }
    modal.hidden = false;
    window.setTimeout(function () { input && input.focus(); }, 20);
  }

  function closeCommand() {
    if (modal) {
      modal.hidden = true;
    }
  }

  qsa('[data-command-open]').forEach(function (trigger) {
    trigger.addEventListener('click', openCommand);
  });
  qsa('[data-command-close]').forEach(function (trigger) {
    trigger.addEventListener('click', closeCommand);
  });

  document.addEventListener('keydown', function (event) {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
      event.preventDefault();
      openCommand();
    }
    if (event.key === 'Escape') {
      closeCommand();
    }
  });

  var searchTimer = null;
  if (input && results) {
    input.addEventListener('input', function () {
      window.clearTimeout(searchTimer);
      searchTimer = window.setTimeout(function () {
        var q = input.value.trim();
        if (!q) {
          results.innerHTML = '';
          return;
        }
        fetch('/search?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
          .then(function (response) { return response.json(); })
          .then(function (payload) {
            results.innerHTML = '';
            (payload.items || []).forEach(function (item) {
              var link = document.createElement('a');
              link.href = '/hymns/' + item.id;
              link.innerHTML = '<strong></strong><span></span>';
              link.querySelector('strong').textContent = item.title_cn || '';
              link.querySelector('span').textContent = item.first_line || item.scripture_refs || '圣诗资料';
              results.appendChild(link);
            });
            if (!results.children.length) {
              results.innerHTML = '<div class="empty-state">没有找到匹配结果。</div>';
            }
          });
      }, 220);
    });
  }
})();

