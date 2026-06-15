import tailwind from '@tailwindcss/postcss'
import cascadeLayers from '@csstools/postcss-cascade-layers'

export default {
    plugins: [
        tailwind(),
        cascadeLayers(),
    ],
}
