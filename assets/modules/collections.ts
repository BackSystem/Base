import { html } from '../functions/Dom'
import { onClick } from '../functions/Event'

function rewriteIndexAttributes(element: Element, oldIndex: number, newIndex: number) {
    const attrs = ['name', 'id', 'for']

    for (const attr of attrs) {
        const value = element.getAttribute(attr)

        if (!value) {
            continue
        }

        const next = value
            .replace(new RegExp(`\\[${oldIndex}\\]`, 'g'), `[${newIndex}]`)
            .replace(new RegExp(`_${oldIndex}_`, 'g'), `_${newIndex}_`)
            .replace(new RegExp(`_${oldIndex}(?=$|_)`, 'g'), `_${newIndex}`)

        if (next !== value) {
            element.setAttribute(attr, next)
        }
    }
}

function getItemIndex(item: HTMLElement): number | null {
    if (item.dataset.index) {
        return parseInt(item.dataset.index, 10)
    }

    const indexed = item.querySelector('[name]') as HTMLElement | null

    if (indexed) {
        const name = indexed.getAttribute('name') || ''

        const match = name.match(/\[(\d+)]/)

        if (match) {
            return parseInt(match[1], 10)
        }
    }

    return null
}

function reindexCollection(container: HTMLElement) {
    const items = container.querySelectorAll<HTMLElement>('.item')

    items.forEach((item, newIndex) => {
        const oldIndex = getItemIndex(item)

        if (oldIndex === null) {
            item.dataset.index = String(newIndex)

            return
        }

        if (oldIndex !== newIndex) {
            item.querySelectorAll('[name],[id],[for]').forEach(element =>
                rewriteIndexAttributes(element as Element, oldIndex, newIndex),
            )
        }

        item.dataset.index = String(newIndex)
    })

    container.dataset.index = String(items.length)
}

onClick('.add-item[data-collection-holder-class]', function () {
    const { collectionHolderClass } = this.dataset

    const container = document.querySelector(`[data-collection="${collectionHolderClass}"]`) as HTMLElement

    if (!container) {
        return
    }

    const { prototype } = container.dataset

    let index = parseInt(container.dataset.index || '0', 10)

    const data = html(prototype.replace(/__name__/g, index.toString()))

    const isPrepend = this.dataset.collectionPrepend !== undefined
    if (isPrepend) {
        container.prepend(data)
    } else {
        container.append(data)
    }

    if (!isPrepend) {
        index += 1

        container.dataset.index = index.toString()
    }

    const inputFile = data.querySelector('input[type="file"]') as HTMLInputElement | null

    if (inputFile) {
        data.classList.add('visually-hidden')

        const filename = data.querySelector('.filename') as HTMLSpanElement | null
        const filesize = data.querySelector('.filesize') as HTMLSpanElement | null

        inputFile.addEventListener('change', () => {
            const file = inputFile.files?.[0]

            if (!file) {
                return
            }

            if (filename) {
                filename.innerText = file.name
            }

            if (filesize && (file as any).size?.sizeFormat) {
                filesize.innerText = (file.size as any).sizeFormat()
            } else if (filesize) {
                filesize.innerText = `${Math.round(file.size / 1024)} KB`
            }

            data.classList.remove('visually-hidden')
        })

        inputFile.click()
    }

    reindexCollection(container)
})

onClick('.delete-item', function () {
    const item = this.closest('.item') as HTMLElement | null

    if (!item) {
        return
    }

    const container = item.closest('[data-collection]') as HTMLElement | null

    item.remove()

    if (container) {
        reindexCollection(container)
    }
})
