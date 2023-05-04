import { Basic, Fetch } from '@backsystem/select'

document.querySelectorAll('select:not([data-url])').forEach((select: HTMLSelectElement) => {
    Basic.get(select)
})

document.querySelectorAll('select[data-url]').forEach((select: HTMLSelectElement) => {
    Fetch.get(select)
})

new MutationObserver(mutations => {
    mutations.forEach(mutation => {
        mutation.addedNodes.forEach(node => {
            if (node instanceof HTMLElement) {
                node.querySelectorAll('select:not([data-url])').forEach((select: HTMLSelectElement) => {
                    Basic.get(select)
                })

                node.querySelectorAll('select[data-url]').forEach((select: HTMLSelectElement) => {
                    Fetch.get(select)
                })
            }
        })
    })
}).observe(document, {
    attributes: false,
    childList: true,
    characterData: false,
    subtree: true,
})