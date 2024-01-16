export {}

declare global {
    interface Element {
        wrapAll(nodes): Element
    }
}

Element.prototype.wrapAll = function (nodes) {
    const parent = nodes[0].parentNode
    const previousSibling = nodes[0].previousSibling

    for (let i = 0; nodes.length - i; this.firstChild === nodes[0] && i++) {
        this.appendChild(nodes[i])
    }

    const nextSibling = previousSibling ? previousSibling.nextSibling : parent.firstChild

    parent.insertBefore(this, nextSibling)

    return this
}
