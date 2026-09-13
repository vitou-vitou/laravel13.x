gsap.registerPlugin(ScrollTrigger);

const lenis = new Lenis();
lenis.on("scroll", ScrollTrigger.update);
gsap.ticker.add((time) => lenis.raf(time * 1000));
gsap.ticker.lagSmoothing(0);

gsap.from(".hero-title", { y: 30, opacity: 0, duration: 1 });
gsap.from(".hero-sub", { y: 20, opacity: 0, duration: 1, delay: 0.3 });

document.querySelectorAll(".reveal").forEach((el) => {
  gsap.to(el, {
    opacity: 1,
    y: 0,
    duration: 0.8,
    scrollTrigger: { trigger: el, start: "top 85%" },
  });
});
