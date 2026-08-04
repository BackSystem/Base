export {}

declare global {
    interface Element {
        wrapAll(nodes: ArrayLike<Node>): Element
    }
}

Element.prototype.wrapAll = function (nodes: ArrayLike<Node>): Element {
    const firstNode = nodes[0]

    if (!firstNode?.parentNode) {
        return this
    }

    const parent = firstNode.parentNode
    const previousSibling = firstNode.previousSibling

    for (let i = 0; i < nodes.length; i++) {
        this.appendChild(nodes[i])
    }

    const nextSibling = previousSibling
        ? previousSibling.nextSibling
        : parent.firstChild

    parent.insertBefore(this, nextSibling)

    return this
}
