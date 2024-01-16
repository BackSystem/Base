export {}

declare global {
	interface String {
		rtrim(string?: string): string

		ltrim(string?: string): string
	}
}

String.prototype.rtrim = function (string: string): string {
	if (string === undefined) string = '\\s'

	return this.replace(new RegExp('[' + string + ']*$'), '')
}

String.prototype.ltrim = function (string: string): string {
	if (string == undefined) string = '\\s';
	return this.replace(new RegExp('^[' + string + ']*'), '')
};