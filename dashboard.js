/* ═══════════════════════════════════════════════
   SASIDA — dashboard.js
   Frontend interactions for the customer portal
   ═══════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', function() {
  'use strict';

  // Hide global loader on dashboard
  var loader = document.getElementById('loader');
  if (loader) loader.classList.add('hidden');

  // 1. Mobile Sidebar Toggle
  var sidebarToggle = document.getElementById('sidebarToggle');
  var sidebar = document.getElementById('dashboardSidebar');
  
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function(e) {
      e.stopPropagation();
      sidebar.classList.toggle('open');
    });

    document.addEventListener('click', function(e) {
      if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== sidebarToggle) {
        sidebar.classList.remove('open');
      }
    });
  }

  // Helper for AJAX post requests
  function ajaxPost(url, data, callback) {
    var params = [];
    for (var key in data) {
      if (data.hasOwnProperty(key)) {
        params.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
      }
    }
    var paramStr = params.join('&');

    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: paramStr
    })
    .then(function(res) {
      return res.json();
    })
    .then(function(json) {
      if (callback) callback(json);
    })
    .catch(function(err) {
      console.error('AJAX Error:', err);
      alert('An error occurred. Please try again.');
    });
  }

  // 2. Wishlist Actions
  document.querySelectorAll('.remove-wishlist-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      var wishId = this.dataset.id;
      if (!wishId) return;

      var row = this.closest('tr') || this.closest('.wishlist-item-card');
      ajaxPost('dashboard.php?page=wishlist&action=remove', { id: wishId }, function(res) {
        if (res.success) {
          if (row) {
            row.style.opacity = '0';
            setTimeout(function() {
              row.remove();
              // Check if table is empty to show empty state
              var remaining = document.querySelectorAll('.remove-wishlist-btn');
              if (remaining.length === 0) {
                location.reload(); // Reload to show empty state
              }
            }, 300);
          }
        } else {
          alert(res.message || 'Could not remove item.');
        }
      });
    });
  });

  // 3. Address List Delete Dialog
  document.querySelectorAll('.delete-address-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      if (!confirm('Are you sure you want to delete this address?')) return;

      var addrId = this.dataset.id;
      var card = this.closest('.address-card');
      ajaxPost('dashboard.php?page=addresses&action=delete', { id: addrId }, function(res) {
        if (res.success) {
          if (card) {
            card.style.opacity = '0';
            setTimeout(function() {
              card.remove();
            }, 300);
          }
        } else {
          alert(res.message || 'Could not delete address.');
        }
      });
    });
  });

  // 4. Notifications actions
  document.querySelectorAll('.mark-read-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      var notifId = this.dataset.id;
      var item = this.closest('.notification-item');
      var badge = this;

      ajaxPost('dashboard.php?page=notifications&action=read', { id: notifId }, function(res) {
        if (res.success) {
          if (item) {
            item.classList.remove('unread');
            badge.style.display = 'none';
          }
        }
      });
    });
  });

  var markAllReadBtn = document.getElementById('markAllRead');
  if (markAllReadBtn) {
    markAllReadBtn.addEventListener('click', function(e) {
      e.preventDefault();
      ajaxPost('dashboard.php?page=notifications&action=read_all', {}, function(res) {
        if (res.success) {
          document.querySelectorAll('.notification-item.unread').forEach(function(item) {
            item.classList.remove('unread');
          });
          document.querySelectorAll('.mark-read-btn').forEach(function(btn) {
            btn.style.display = 'none';
          });
        }
      });
    });
  });

  document.querySelectorAll('.delete-notif-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      var notifId = this.dataset.id;
      var item = this.closest('.notification-item');

      ajaxPost('dashboard.php?page=notifications&action=delete', { id: notifId }, function(res) {
        if (res.success) {
          if (item) {
            item.style.opacity = '0';
            setTimeout(function() {
              item.remove();
              var remaining = document.querySelectorAll('.delete-notif-btn');
              if (remaining.length === 0) {
                location.reload(); // Reload to show empty state
              }
            }, 300);
          }
        }
      });
    });
  });

  // 5. Recently Viewed History
  document.querySelectorAll('.remove-history-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      var rvId = this.dataset.id;
      var row = this.closest('tr');

      ajaxPost('dashboard.php?page=recently_viewed&action=remove', { id: rvId }, function(res) {
        if (res.success) {
          if (row) {
            row.style.opacity = '0';
            setTimeout(function() {
              row.remove();
              var remaining = document.querySelectorAll('.remove-history-btn');
              if (remaining.length === 0) {
                location.reload();
              }
            }, 300);
          }
        }
      });
    });
  });

  var clearHistoryBtn = document.getElementById('clearHistory');
  if (clearHistoryBtn) {
    clearHistoryBtn.addEventListener('click', function(e) {
      e.preventDefault();
      if (!confirm('Clear all browsing history?')) return;

      ajaxPost('dashboard.php?page=recently_viewed&action=clear', {}, function(res) {
        if (res.success) {
          location.reload();
        }
      });
    });
  }

});
