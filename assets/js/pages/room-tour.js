// ========================================
// ROOM TOUR 360 DERAJAT
// Kost Pak Sapriadi
// ========================================

const panoramaElement = document.querySelector('#panorama');
const captionElement = document.querySelector('#room-tour-caption');
const roomButtons = document.querySelectorAll(
  '.room-tour-buttons [data-scene]'
);

// Berhenti jika elemen HTML belum tersedia.
if (panoramaElement && window.pannellum) {

  // ======================================
  // 1. DATA PANORAMA CONTOH
  // ======================================
  // Panorama ini hanya untuk pengujian.
  // Ganti URL dengan foto panorama 360° asli
  // setelah foto kost tersedia.

  const rooms = {
    pintu: {
      title: 'Pintu Masuk',
      panorama: 'https://pannellum.org/images/alma.jpg',
      next: 'tengah',
      nextLabel: 'Lihat Ruang Tengah'
    },

    tengah: {
      title: 'Ruang Tengah',
      panorama: 'https://pannellum.org/images/tocopilla.jpg',
      next: 'kamar1',
      nextLabel: 'Lihat Kamar 1'
    },

    kamar1: {
      title: 'Kamar 1',
      panorama: 'https://pannellum.org/images/lascar.jpg',
      next: 'kamar2',
      nextLabel: 'Lihat Kamar 2'
    },

    kamar2: {
      title: 'Kamar 2',
      panorama: 'https://pannellum.org/images/charles-street.jpg',
      next: 'dapur',
      nextLabel: 'Lihat Dapur'
    },

    dapur: {
      title: 'Dapur',
      panorama: 'https://pannellum.org/images/alma.jpg',
      next: 'pintu',
      nextLabel: 'Kembali ke Pintu Masuk'
    }
  };

  // ======================================
  // 2. MEMBUAT PENAMPIL PANORAMA
  // ======================================

  let tourViewer;

  function bukaRuangan(idRuangan) {
    const room = rooms[idRuangan];

    if (!room) return;

    // Tandai tombol ruangan yang sedang aktif.
    roomButtons.forEach(button => {
      const aktif = button.dataset.scene === idRuangan;

      button.classList.toggle('active', aktif);
      button.setAttribute('aria-pressed', String(aktif));
    });

    // Perbarui keterangan ruangan.
    if (captionElement) {
      captionElement.textContent =
        `Panorama contoh: ${room.title}`;
    }

    // Muat panorama untuk ruangan yang dipilih.
    if (tourViewer) {
      tourViewer.destroy();
    }

    tourViewer = window.pannellum.viewer('panorama', {
      type: 'equirectangular',
      panorama: room.panorama,
      autoLoad: true,
      showZoomCtrl: true,
      showFullscreenCtrl: true,
      mouseZoom: true,
      draggable: true,
      keyboardZoom: true,
      hfov: 100,

      // Hotspot untuk menuju ruangan selanjutnya.
      hotSpots: [
        {
          pitch: -5,
          yaw: 0,
          type: 'scene',
          text: room.nextLabel,
          sceneId: room.next
        }
      ]
    });
  }

  // ======================================
  // 3. TOMBOL PINDAH RUANGAN
  // ======================================

  roomButtons.forEach(button => {
    button.addEventListener('click', () => {
      bukaRuangan(button.dataset.scene);
    });
  });

  // ======================================
  // 4. RUANGAN PERTAMA SAAT DIBUKA
  // ======================================

  bukaRuangan('pintu');

} else if (captionElement) {
  captionElement.textContent =
    'Room Tour tidak dapat dimuat. Periksa koneksi internet dan library Pannellum.';
}