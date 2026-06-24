<?php
$page_title = "Kontak";
include 'includes/header.php';
?>

<!-- Bootstrap Icons for Contact Page -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />

<div class="contact-container">
  <form id="whatsappForm" class="scroll-animate fade-right" data-delay="0">
    <h3 class="scroll-animate fade-down" data-delay="100">Kirim Pesan</h3>
    <input type="text" id="name" placeholder="Nama Anda" required class="scroll-animate fade-up" data-delay="200" />
    <input type="tel" id="phone" placeholder="Nomor Telepon Anda" required class="scroll-animate fade-up" data-delay="300"/>
    <textarea id="message" rows="5" placeholder="Tulis pesan Anda..." required class="scroll-animate fade-up" data-delay="400"></textarea>
    <button type="submit" class="scroll-animate zoom-in" data-delay="500">Kirim Pesan</button>
  </form>

  <div class="contact-info scroll-animate fade-left" data-delay="0">
    <h3 class="scroll-animate fade-down" data-delay="100">Alamat Kami</h3>
    <p class="scroll-animate fade-up" data-delay="200"><i class="bi bi-geo-alt-fill"></i><strong>Alamat:</strong> Jl.Raya mauk km 12, Rt 02, RW 02 Desa Pisangan Jaya Kosambi Sepatan, Kabupaten Tangerang, Banten 15520</p>
    <p class="scroll-animate fade-up" data-delay="300"><i class="bi bi-telephone-fill"></i><strong>Telepon:</strong> 0812-8447-7303</p>
    <p class="scroll-animate fade-up" data-delay="400"><i class="bi bi-envelope-fill"></i><strong>Email:</strong> sarisalon11@gmail.com</p>

    <iframe 
      src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3967.1486778314056!2d106.56465702377834!3d-6.110675959926223!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6a010cccdc17a9%3A0xac946657f052f729!2sSari%20Salon%20kecantikan!5e0!3m2!1sen!2sid!4v1762103474045!5m2!1sen!2sid"
      allowfullscreen="" loading="lazy" class="scroll-animate zoom-in" data-delay="500"></iframe>
  </div>
</div>

<script>
  // Script kirim pesan ke WhatsApp
  const form = document.getElementById("whatsappForm");

  if (form) {
    form.addEventListener("submit", function(e) {
      e.preventDefault();

      const name = document.getElementById("name").value.trim();
      const phone = document.getElementById("phone").value.trim();
      const message = document.getElementById("message").value.trim();

      if (!name || !phone || !message) {
        alert("Harap isi semua data sebelum mengirim pesan!");
        return;
      }

      const phoneNumber = "6285773215793"; // Nomor WhatsApp tujuan salon

      const text = 
        `Halo Beauty Sari Salon,%0A` +
        `Nama: ${name}%0A` +
        `Nomor: ${phone}%0A` +
        `Pesan:%0A${message}`;

      const url = `https://wa.me/${phoneNumber}?text=${text}`;

      window.open(url, "_blank");
      form.reset();
    });

    // prevent default reload on submit
    const btn = form.querySelector("button[type='submit']");
    if (btn) {
      btn.addEventListener("click", function(event) {
        event.preventDefault();
        form.requestSubmit();
      });
    }
  }
</script>

<?php include 'includes/footer.php'; ?>
