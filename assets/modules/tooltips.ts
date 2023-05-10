import { Tooltip } from 'bootstrap'

document.querySelectorAll('[title]').forEach(element => {
    Tooltip.getOrCreateInstance(element, {
        html: true
    })
})

new MutationObserver(() => {
    document.querySelectorAll('[title]').forEach(element => {
        Tooltip.getOrCreateInstance(element, {
            html: true
        })
    })
}).observe(document, {
    attributes: false,
    childList: true,
    characterData: false,
    subtree: true,
})