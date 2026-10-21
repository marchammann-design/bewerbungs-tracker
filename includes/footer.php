    </main>

    <footer class="site-footer">
        <p>&copy; <?= date('Y') ?> Bewerbungs-Tracker by Hammann Marc</p>
    </footer>

        <!-- Script für Burger-Menü -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const burgerBtn = document.getElementById('burger-btn');
            const mainNav = document.getElementById('main-nav');

            if (burgerBtn && mainNav) {
                burgerBtn.addEventListener('click', function() {
                    mainNav.classList.toggle('active');
                    burgerBtn.classList.toggle('active'); // <-- Neu: Button wird zum X
                });
            }
        });
    </script>

</body>
</html>