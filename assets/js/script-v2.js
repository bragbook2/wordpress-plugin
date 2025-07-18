function initSlickSlider() {
  const $track = jQuery('#bbrag-thumbnail-track');
  const $mainImage = jQuery('#bbrag-active-image');

  $track.slick({
    slidesToShow: 4,
    slidesToScroll: 1,
    arrows: true,
    infinite: true,
    focusOnSelect: true,
    prevArrow: jQuery('.bbrag-thumb-arrow-left'),
    nextArrow: jQuery('.bbrag-thumb-arrow-right'),
  });

  const $first = $track.find('img').first();
  if ($first.length) {
    $mainImage.attr('src', $first.attr('src'));
    $first.addClass('active');
  }

  $track.on('click', 'img', function () {
    $track.find('img').removeClass('active');
    jQuery(this).addClass('active');
    $mainImage.attr('src', jQuery(this).attr('src'));
  });

  $track.on('afterChange', function () {
    const $currentImg = $track.find('.slick-current');
    if ($currentImg.length) {
      $track.find('img').removeClass('active');
      $currentImg.addClass('active');
      $mainImage.attr('src', $currentImg.attr('src'));
    }
  });

}

jQuery(document).ready(function ($) {
  setTimeout(() => {
    if ($.fn.slick) {
      initSlickSlider();
    }
  }, 3000);
});

function bbragSetActiveImage(thumb) {
  const mainImg = document.getElementById('bbrag-active-image');
  mainImg.src = thumb.src;

  const thumbs = document.querySelectorAll('#bbrag-thumbnail-track img');
  thumbs.forEach(img => img.classList.remove('active'));
  thumb.classList.add('active');
}


function toggleDropdown() {
  const list = document.getElementById("bbrag-gallery-list");
  const headerArrow = document.querySelector('.bbrag-dropdown-header .bbrag-arrow');
  list.style.display = list.style.display === "block" ? "none" : "block";
  headerArrow.style.transform = headerArrow.style.transform === "rotate(180deg)" ? "rotate(0deg)" : "rotate(180deg)";
}

function filterGalleryOptions() {
  const input = document.getElementById("bbrag-search").value.toLowerCase();
  const listItems = document.querySelectorAll("#bbrag-gallery-list li");

  listItems.forEach((item) => {
    const text = item.textContent.toLowerCase();
    item.style.display = text.includes(input) ? "block" : "none";
  });
}
document.addEventListener('DOMContentLoaded', () => {
  if (bb_plugin_data?.designVersion === 'v2') {
    initGalleryDropdown();
  }
});

function initGalleryDropdown() {
  const header = document.querySelector('.bbrag-dropdown-header');
  const dropdownList = document.getElementById('bbrag-gallery-list');
  const anchors = dropdownList?.querySelectorAll('a');

  if (!header || !dropdownList || !anchors) return;
  const selectedSlug = getSelectedSlugFromURL();
  if (!selectedSlug) return;

  highlightSelectedItem(header, selectedSlug, anchors);
}

function getSelectedSlugFromURL() {
  const pathParts = window.location.pathname.split('/');
  return pathParts.length > 2 ? pathParts[2] : null;
}

function updateDropdownHeader(headerElement, text) {
  headerElement.childNodes[0].nodeValue = `${text} `;
}

function highlightSelectedItem(header, selectedSlug, anchors) {
  anchors.forEach(anchor => {
    const href = anchor.getAttribute('href') || '';
    if (href.includes(`/${selectedSlug}/`)) {
      const parentLi = anchor.parentElement;
      if (parentLi) {
        parentLi.style.backgroundColor = '#f0f0f0';
        updateDropdownHeader(header, parentLi.textContent);
        anchor.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    }
  });
}