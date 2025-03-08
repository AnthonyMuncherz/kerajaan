        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="content has-text-centered">
            <p>
                <strong><?= SITE_NAME ?></strong> - Sistem Pengurusan Permohonan Keluar
                <br>
                &copy; <?= date('Y') ?> Institut Pendidikan Guru Kampus Bahasa Melayu (IPG KBM)
            </p>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize navbar burger menu for mobile
            const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);
            if ($navbarBurgers.length > 0) {
                $navbarBurgers.forEach(el => {
                    el.addEventListener('click', () => {
                        const target = el.dataset.target;
                        const $target = document.getElementById(target);
                        el.classList.toggle('is-active');
                        $target.classList.toggle('is-active');
                    });
                });
            }
            
            // Initialize notification dismiss
            (document.querySelectorAll('.notification .delete') || []).forEach(($delete) => {
                const $notification = $delete.parentNode;
                $delete.addEventListener('click', () => {
                    $notification.parentNode.removeChild($notification);
                });
            });
            
            // Initialize date pickers
            if (document.querySelector('.datepicker')) {
                flatpickr('.datepicker', {
                    dateFormat: 'Y-m-d',
                    allowInput: true
                });
            }
            
            // Initialize time pickers
            if (document.querySelector('.timepicker')) {
                flatpickr('.timepicker', {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: 'H:i',
                    time_24hr: true,
                    allowInput: true
                });
            }
        });
    </script>
</body>
</html> 