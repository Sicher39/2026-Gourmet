import { ScrollTrigger } from 'gsap/ScrollTrigger'
import gsap from 'gsap'

let isRegistered = false

export function registerGsapPlugins() {
  if (!isRegistered) {
    gsap.registerPlugin(ScrollTrigger)
    isRegistered = true
  }

  return gsap
}

export { ScrollTrigger }
