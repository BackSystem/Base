import { html } from '../functions/Dom'
import { onClick } from '../functions/Event'

onClick('.add-item[data-collection-holder-class]', function () {
	const { collectionHolderClass } = this.dataset

	const container = document.querySelector(`[data-collection="${collectionHolderClass}"]`) as HTMLElement

	if (container) {
		const { prototype } = container.dataset
		let { index } = container.dataset

		const data = html(prototype.replace(/__name__/g, index))

		container.appendChild(data)

		index += 1

		container.dataset.index = index
	}
})

onClick('.delete-item', function () {
	const item = this.closest('.item')

	if (item) {
		item.remove()
	}
})