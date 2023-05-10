import { html } from '../functions/Dom'
import { onClick } from '../functions/Event'

onClick('.add-item[data-collection-holder-class]', function () {
    const { collectionHolderClass } = this.dataset

    const container = document.querySelector(`[data-collection="${collectionHolderClass}"]`) as HTMLElement

    if (container) {
        const { prototype } = container.dataset

        let index = parseInt(container.dataset.index)

        const data = html(prototype.replace(/__name__/g, index.toString()))

        container.appendChild(data)

        index += 1

        container.dataset.index = index.toString()

        const inputFile = data.querySelector('input[type="file"]') as HTMLInputElement

        if (inputFile) {
            data.classList.add('visually-hidden')

            const filename = data.querySelector('.filename') as HTMLSpanElement
            const filesize = data.querySelector('.filesize') as HTMLSpanElement

            inputFile.addEventListener('change', () => {
                const file = inputFile.files[0]

                if (filename) {
                    filename.innerText = file.name
                }

                if (filesize) {
                    filesize.innerText = file.size.sizeFormat()
                }

                data.classList.remove('visually-hidden')
            })

            inputFile.click()
        }
    }
})

onClick('.delete-item', function () {
    const item = this.closest('.item')

    if (item) {
        item.remove()
    }
})