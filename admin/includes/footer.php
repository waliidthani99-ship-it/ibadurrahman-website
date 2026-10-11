  </main>
</div>
</div>
<script>
(function () {
  var toggle  = document.getElementById('menuToggle');
  var sidebar = document.getElementById('adminSidebar');
  var overlay = document.getElementById('sidebarOverlay');

  if (toggle && sidebar && overlay) {
    function openSidebar() {
      sidebar.classList.add('is-open');
      sidebar.classList.remove('-translate-x-full');
      overlay.classList.remove('hidden');
    }
    function closeSidebar() {
      sidebar.classList.remove('is-open');
      sidebar.classList.add('-translate-x-full');
      overlay.classList.add('hidden');
    }
    toggle.addEventListener('click', function () {
      var isOpen = sidebar.classList.contains('is-open') || !sidebar.classList.contains('-translate-x-full');
      if (isOpen) closeSidebar(); else openSidebar();
    });
    overlay.addEventListener('click', closeSidebar);
    sidebar.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.matchMedia('(max-width: 767px)').matches) closeSidebar();
      });
    });
  }
})();

(function () {
  var btn  = document.getElementById('adminMenuBtn');
  var menu = document.getElementById('adminMenu');
  if (!btn || !menu) return;
  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    menu.classList.toggle('hidden');
  });
  document.addEventListener('click', function () { menu.classList.add('hidden'); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') menu.classList.add('hidden');
  });
})();
</script>
</body>
</html>
