/**
 * KampusLMS Diorama Animations Orchestrator
 *
 * Implements restrained, organic micro-animations using Anime.js v3:
 * - Continuous Levitation with Inverse Contact Shadow (Float Doodle)
 * - Continuous Organic Breathing & Shadow Pulse (Loving Doodle)
 * - Atmospheric Canopy Breeze (Corner Leaf Canopies)
 * - Tactile Spring Feedback (Buttons)
 * - Focus Lean-In (Form inputs and hero characters)
 * - Strict prefers-reduced-motion accessibility compliance with dynamic listener
 * - Defensive null-guarding for zero runtime console errors
 */

import anime from 'animejs/lib/anime.es.js';

// Media query listener for prefers-reduced-motion
const motionQuery = typeof window !== 'undefined' && window.matchMedia
  ? window.matchMedia('(prefers-reduced-motion: reduce)')
  : null;

/**
 * Check whether the user prefers reduced motion
 * @returns {boolean}
 */
export function isReducedMotion() {
  return motionQuery ? Boolean(motionQuery.matches) : false;
}

// Registry of running continuous Anime instances
let activeLoopAnimations = [];
let motionListenerBound = false;

/**
 * Dynamically disable CSS transitions on target elements to prevent
 * conflict with Anime.js transform interpolation
 * @param {NodeList|Array<HTMLElement>} elements
 */
function disableTransitions(elements) {
  if (!elements) return;
  elements.forEach((el) => {
    if (el && el.style) {
      el.style.transition = 'none';
    }
  });
}

/**
 * Reset inline transforms on all animated elements when reduced motion is activated
 */
function resetTransforms() {
  const elements = document.querySelectorAll(
    '[data-anime="float"], .anime-float-character, ' +
    '[data-anime="float-shadow"], .anime-float-shadow, ' +
    '[data-anime="loving"], .anime-loving-character, ' +
    '[data-anime="loving-shadow"], .anime-loving-shadow, ' +
    '.diorama-canopy, .diorama-canopy svg, svg.diorama-canopy, .canopy-left, .canopy-right, ' +
    '[data-anime="auth-hero"], .hero-diorama .hero-art, ' +
    '#email, #password, [data-tactile="spring"]'
  );

  elements.forEach((el) => {
    if (el && el.style) {
      el.style.transform = '';
    }
  });
}

/**
 * Handle dynamic changes to OS prefers-reduced-motion preference
 * @param {MediaQueryListEvent} event
 */
function handleMotionPreferenceChange(event) {
  const reduced = event.matches;
  if (reduced) {
    // Pause and clear all continuous animations
    activeLoopAnimations.forEach((anim) => {
      if (anim && typeof anim.pause === 'function') {
        anim.pause();
      }
    });
    activeLoopAnimations = [];
    resetTransforms();
  } else {
    // Restart continuous loops
    startContinuousLoops();
  }
}

/**
 * Initialize Continuous Idle Levitation & Inverse Shadow on opendoodles-float.svg
 * Target: #course-empty-float-hero, [data-anime="float"], .anime-float-character
 * Shadow: [data-anime="float-shadow"], .anime-float-shadow
 */
function initFloatAnimation() {
  const characters = document.querySelectorAll(
    '#course-empty-float-hero img, [data-anime="float"], .anime-float-character'
  );
  const shadows = document.querySelectorAll(
    '#course-empty-float-hero .anime-float-shadow, [data-anime="float-shadow"], .anime-float-shadow'
  );

  if (characters.length === 0 && shadows.length === 0) {
    return;
  }

  disableTransitions(characters);
  disableTransitions(shadows);

  if (characters.length > 0) {
    const charAnim = anime({
      targets: Array.from(characters),
      translateY: [-8, 8],
      direction: 'alternate',
      loop: true,
      easing: 'easeInOutSine',
      duration: 3200,
    });
    activeLoopAnimations.push(charAnim);
  }

  if (shadows.length > 0) {
    const shadowAnim = anime({
      targets: Array.from(shadows),
      scaleX: [0.85, 1.15],
      direction: 'alternate',
      loop: true,
      easing: 'easeInOutSine',
      duration: 3200,
    });
    activeLoopAnimations.push(shadowAnim);
  }
}

/**
 * Initialize Continuous Organic Breathing on opendoodles-loving.svg
 * Target: #tentang-loving-hero img, [data-anime="loving"], .anime-loving-character
 * Shadow: #tentang-loving-hero .anime-loving-shadow, [data-anime="loving-shadow"], .anime-loving-shadow
 */
function initLovingAnimation() {
  const characters = document.querySelectorAll(
    '#tentang-loving-hero img, [data-anime="loving"], .anime-loving-character'
  );
  const shadows = document.querySelectorAll(
    '#tentang-loving-hero .anime-loving-shadow, [data-anime="loving-shadow"], .anime-loving-shadow'
  );

  if (characters.length === 0 && shadows.length === 0) {
    return;
  }

  disableTransitions(characters);
  disableTransitions(shadows);

  if (characters.length > 0) {
    const charAnim = anime({
      targets: Array.from(characters),
      scale: [1, 1.025, 1],
      translateY: [0, -3, 0],
      loop: true,
      easing: 'easeInOutQuad',
      duration: 3800,
    });
    activeLoopAnimations.push(charAnim);
  }

  if (shadows.length > 0) {
    const shadowAnim = anime({
      targets: Array.from(shadows),
      scaleX: [1, 1.05, 1],
      loop: true,
      easing: 'easeInOutQuad',
      duration: 3800,
    });
    activeLoopAnimations.push(shadowAnim);
  }
}

/**
 * Initialize Atmospheric Canopy Breeze on corner leaf canopies
 * Target: .canopy-left, .canopy-right, svg.diorama-canopy, .diorama-canopy
 */
function initCanopyAnimation() {
  const leftCanopies = document.querySelectorAll('.canopy-left');
  const rightCanopies = document.querySelectorAll('.canopy-right');
  const otherCanopies = document.querySelectorAll(
    'svg.diorama-canopy:not(.canopy-left):not(.canopy-right)'
  );

  if (leftCanopies.length === 0 && rightCanopies.length === 0 && otherCanopies.length === 0) {
    return;
  }

  disableTransitions(leftCanopies);
  disableTransitions(rightCanopies);
  disableTransitions(otherCanopies);

  if (leftCanopies.length > 0) {
    leftCanopies.forEach((el) => {
      el.style.transformOrigin = 'top left';
    });
    const leftAnim = anime({
      targets: Array.from(leftCanopies),
      rotate: [-2, 2.5],
      direction: 'alternate',
      loop: true,
      easing: 'easeInOutSine',
      duration: 4500,
    });
    activeLoopAnimations.push(leftAnim);
  }

  if (rightCanopies.length > 0) {
    rightCanopies.forEach((el) => {
      el.style.transformOrigin = 'top right';
    });
    const rightAnim = anime({
      targets: Array.from(rightCanopies),
      rotate: [2.5, -2],
      direction: 'alternate',
      loop: true,
      easing: 'easeInOutSine',
      duration: 4500,
    });
    activeLoopAnimations.push(rightAnim);
  }

  if (otherCanopies.length > 0) {
    const otherAnim = anime({
      targets: Array.from(otherCanopies),
      rotate: [-2, 2.5],
      direction: 'alternate',
      loop: true,
      easing: 'easeInOutSine',
      duration: 4500,
    });
    activeLoopAnimations.push(otherAnim);
  }
}

/**
 * Start or restart all continuous animation loops if reduced motion is inactive
 */
function startContinuousLoops() {
  // Stop any existing loop instances to avoid duplication
  activeLoopAnimations.forEach((anim) => {
    if (anim && typeof anim.pause === 'function') {
      anim.pause();
    }
  });
  activeLoopAnimations = [];

  if (isReducedMotion()) {
    resetTransforms();
    return;
  }

  initFloatAnimation();
  initLovingAnimation();
  initCanopyAnimation();
}

/**
 * Initialize Tactile Spring Feedback on buttons
 * Targets: [data-tactile="spring"], .demo-fill-btn, .greeting-action-btn
 */
function initTactileFeedback() {
  const buttons = document.querySelectorAll(
    '[data-tactile="spring"], .demo-fill-btn, .greeting-action-btn'
  );

  if (buttons.length === 0) return;

  buttons.forEach((btn) => {
    if (btn._hasTactileHook) return;
    btn._hasTactileHook = true;

    btn.addEventListener('pointerdown', () => {
      if (isReducedMotion()) return;

      btn.style.transition = 'none';
      anime.remove(btn);
      anime({
        targets: btn,
        scale: [1, 1.06, 1],
        duration: 400,
        easing: 'easeOutElastic(1, .8)',
      });
    });
  });
}

/**
 * Initialize Input Focus Lean-In on login page
 * Targets: #email, #password, and backdrop heroes ([data-anime="auth-hero"], .hero-diorama .hero-art)
 */
function initFocusLeanIn() {
  const inputs = document.querySelectorAll('#email, #password');
  const authHeroes = document.querySelectorAll(
    '[data-anime="auth-hero"], .hero-diorama .hero-art'
  );

  if (inputs.length === 0) return;

  disableTransitions(inputs);
  disableTransitions(authHeroes);

  inputs.forEach((input) => {
    if (input._hasFocusLeanHook) return;
    input._hasFocusLeanHook = true;

    input.addEventListener('focus', () => {
      if (isReducedMotion()) return;

      // Animate input
      anime.remove(input);
      anime({
        targets: input,
        scale: 1.015,
        translateY: -1.5,
        duration: 350,
        easing: 'easeOutQuad',
      });

      // Animate hero lean-in
      if (authHeroes.length > 0) {
        anime.remove(Array.from(authHeroes));
        anime({
          targets: Array.from(authHeroes),
          scale: 1.02,
          translateY: -2,
          duration: 350,
          easing: 'easeOutQuad',
        });
      }
    });

    input.addEventListener('blur', () => {
      if (isReducedMotion()) return;

      // Return input to normal
      anime.remove(input);
      anime({
        targets: input,
        scale: 1,
        translateY: 0,
        duration: 300,
        easing: 'easeOutQuad',
      });

      // Return heroes to normal
      if (authHeroes.length > 0) {
        anime.remove(Array.from(authHeroes));
        anime({
          targets: Array.from(authHeroes),
          scale: 1,
          translateY: 0,
          duration: 300,
          easing: 'easeOutQuad',
        });
      }
    });
  });
}

/**
 * Main entry point: Initialize all KampusLMS diorama animations & interactions
 */
export function initDioramaAnimations() {
  if (typeof window === 'undefined' || typeof document === 'undefined') {
    return;
  }

  // Bind interactive handlers (tactile button spring, input focus lean-in)
  initTactileFeedback();
  initFocusLeanIn();

  // Start continuous loops (levitation, breathing, canopy)
  startContinuousLoops();

  // Bind prefers-reduced-motion dynamic listener once
  if (!motionListenerBound && motionQuery) {
    if (typeof motionQuery.addEventListener === 'function') {
      motionQuery.addEventListener('change', handleMotionPreferenceChange);
    } else if (typeof motionQuery.addListener === 'function') {
      motionQuery.addListener(handleMotionPreferenceChange);
    }
    motionListenerBound = true;
  }
}

export default initDioramaAnimations;
