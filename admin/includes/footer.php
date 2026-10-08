</div>
<script>
(function(){
  var toggle = document.getElementById('menuToggle');
  var sidebar = document.getElementById('adminSidebar');
  var overlay = document.getElementById('sidebarOverlay');
  if (!toggle || !sidebar || !overlay) return;
  function open(){ sidebar.classList.remove('-translate-x-full'); overlay.classList.remove('hidden'); }
  function close(){ sidebar.classList.add('-translate-x-full'); overlay.classList.add('hidden'); }
  toggle.addEventListener('click', function(){ sidebar.classList.contains('-translate-x-full') ? open() : close(); });
  overlay.addEventListener('click', close);
})();
</script>
</body>
</html>