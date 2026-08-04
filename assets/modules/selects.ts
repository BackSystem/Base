import {Basic, Fetch} from '@backsystem/select'

document.querySelectorAll<HTMLSelectElement>('select:not([data-url])').forEach(select => {
    Basic.get(select)
})

document.querySelectorAll<HTMLSelectElement>('select[data-url]').forEach(select => {
    Fetch.get(select)
})

new MutationObserver(mutations => {
    mutations.forEach(mutation => {
        mutation.addedNodes.forEach(node => {
            if (node instanceof HTMLElement) {
                node.querySelectorAll<HTMLSelectElement>('select:not([data-url])').forEach(select => {
                    Basic.get(select)
                })

                node.querySelectorAll<HTMLSelectElement>('select[data-url]').forEach(select => {
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
