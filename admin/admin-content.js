(function() {
  'use strict';

  var form = document.getElementById('contentForm');
  var saveMsg = document.getElementById('saveMessage');

  function getContent() {
    return JSON.parse(localStorage.getItem('sasida_content') || '{}');
  }

  function saveContent(content) {
    localStorage.setItem('sasida_content', JSON.stringify(content));
  }

  function loadContent() {
    var content = getContent();
    var hero = content.hero || {};
    var about = content.about || {};
    var contact = content.contact || {};

    document.getElementById('heroEyebrow').value = hero.eyebrow || '';
    document.getElementById('heroTitle').value = hero.title || '';
    document.getElementById('heroSub').value = hero.subtitle || '';
    document.getElementById('heroPrimaryCta').value = hero.primaryCta || '';
    document.getElementById('heroSecondaryCta').value = hero.secondaryCta || '';

    document.getElementById('aboutTag').value = about.tag || '';
    document.getElementById('aboutTitle').value = about.title || '';
    document.getElementById('aboutDesc1').value = about.desc1 || '';
    document.getElementById('aboutDesc2').value = about.desc2 || '';
    document.getElementById('aboutRating').value = about.rating || '';

    document.getElementById('contactPhone').value = contact.phone || '';
    document.getElementById('contactEmail').value = contact.email || '';
    document.getElementById('contactWhatsApp').value = contact.whatsapp || '';
    document.getElementById('contactInstagram').value = contact.instagram || '';
    document.getElementById('contactInstagramText').value = contact.instagramText || '';
    document.getElementById('contactTikTok').value = contact.tiktok || '';
    document.getElementById('contactTikTokText').value = contact.tiktokText || '';
    document.getElementById('contactAddress').value = contact.address || '';
    document.getElementById('contactHours').value = contact.hours || '';
  }

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    var content = getContent();

    content.hero = {
      eyebrow: document.getElementById('heroEyebrow').value.trim(),
      title: document.getElementById('heroTitle').value.trim(),
      subtitle: document.getElementById('heroSub').value.trim(),
      primaryCta: document.getElementById('heroPrimaryCta').value.trim(),
      primaryLink: 'shop.html',
      secondaryCta: document.getElementById('heroSecondaryCta').value.trim(),
      secondaryLink: 'shop.html'
    };

    content.about = {
      tag: document.getElementById('aboutTag').value.trim(),
      title: document.getElementById('aboutTitle').value.trim(),
      desc1: document.getElementById('aboutDesc1').value.trim(),
      desc2: document.getElementById('aboutDesc2').value.trim(),
      rating: document.getElementById('aboutRating').value.trim()
    };

    content.contact = {
      phone: document.getElementById('contactPhone').value.trim(),
      email: document.getElementById('contactEmail').value.trim(),
      whatsapp: document.getElementById('contactWhatsApp').value.trim(),
      instagram: document.getElementById('contactInstagram').value.trim(),
      instagramText: document.getElementById('contactInstagramText').value.trim(),
      tiktok: document.getElementById('contactTikTok').value.trim(),
      tiktokText: document.getElementById('contactTikTokText').value.trim(),
      address: document.getElementById('contactAddress').value.trim(),
      hours: document.getElementById('contactHours').value.trim()
    };

    // Also preserve promotions and testimonials
    if (!content.promotions) content.promotions = [];
    if (!content.testimonials) content.testimonials = [];

    saveContent(content);
    saveMsg.style.display = 'inline';
    setTimeout(function() {
      saveMsg.style.display = 'none';
    }, 3000);
  });

  loadContent();
})();