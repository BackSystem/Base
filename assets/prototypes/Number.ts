export {}

declare global {
    interface Number {
        pad(number?: number): string

        sizeFormat(decimals?: number): string
    }
}

Number.prototype.pad = function (number: number = 2): string {
    return (new Array(number).join('0') + this).slice(-number)
}

Number.prototype.sizeFormat = function (decimals: number = 2): string {
    const i = Math.floor(Math.log(this) / Math.log(1024))

    return (this / Math.pow(1024, i)).toFixed(decimals).replace('.', ',') + ' ' + ['o', 'ko', 'Mo', 'Go', 'To'][i]
}