<script setup>
import { onMounted, ref, toRaw, onBeforeUnmount, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import dayjs from 'dayjs'
const clientName = computed(() => usePage().props.clientName)
const styleNumber = computed(() => Number(usePage().props.styleNumber))
const fontNumber = computed(() => Number(usePage().props.fontNumber))
var intervalLines = null
const lines = ref(null)
const lines1 = ref(null)
const lines2 = ref(null)
const lines3 = ref(null)
const fetchLines = () => {
    axios.get('/api/monitor')
        .then((response) => {
            lines.value = toRaw(response.data.data)
            if (lines.value.length < 60) {
                lines1.value = lines.value.slice()
                lines2.value = null
                lines3.value = null
            } else if (lines.value.length >= 60 && lines.value.length < 90) {
                lines1.value = lines.value.slice(0, Math.floor(lines.value.length / 2))
                lines2.value = lines.value.slice(Math.floor(lines.value.length / 2, lines.value.length))
                lines3.value = null
            } else {
                lines1.value = lines.value.slice(0, Math.floor(lines.value.length / 3))
                lines2.value = lines.value.slice(Math.floor(lines.value.length / 3), Math.floor(lines.value.length * 2 / 3))
                lines3.value = lines.value.slice(Math.floor(lines.value.length * 2 / 3, lines.value.length))
            }
        })
        .catch(function (error) {
            console.log(error)
        })
}
onMounted(() => {
    fetchLines()
    intervalLines = setInterval(fetchLines, 2500)
})
onBeforeUnmount(() => {
    clearInterval(intervalLines)
})
</script>
<template>
    <div class="text-lg" :class="{
            'text-zinc-700': styleNumber === 1, 
            'text-cyan-800': styleNumber === 2,
            'font-oswald': fontNumber === 1,
            'font-titilliumweb': fontNumber === 2,
            'font-librebaskerville': fontNumber === 3,
            'font-firasanscondensed': fontNumber === 4,
            'font-yanonekaffeesatz': fontNumber === 5,
            'font-archivonarrow': fontNumber === 6,
            'font-khand': fontNumber === 7,
            'font-voltaire': fontNumber === 8,
            'font-mirza': fontNumber === 9,
            'font-geo text-xl': fontNumber === 10,
            'font-amarante': fontNumber === 11,
            'font-sharetech': fontNumber === 12,
            'font-iceland': fontNumber === 13,
            'font-genos text-xl': fontNumber === 14
        }">
        <div class="flex justify-center">
            <h1 class="mt-2 mb-4 font-bold text-4xl">:<span class="ml-1">:</span> {{ clientName }} <span class="mr-1">:</span>:</h1>
        </div>
        <div class="flex justify-center">
            <table :class="{'bg-zinc-50 border border-zinc-500': styleNumber === 1, 'bg-slate-50 border border-slate-500': styleNumber === 2}">
                <tr>
                    <th class="p-1 font-semibold border-b whitespace-nowrap"
                        :class="{'bg-zinc-300 border-zinc-500': styleNumber === 1, 'bg-slate-300 border-slate-500': styleNumber === 2}">When ?</th>
                    <th v-if="styleNumber === 1" class="thStyleOne">Command</th>
                    <th v-if="styleNumber === 2" class="thStyleTwo">Command</th>
                    <th v-if="styleNumber === 1" class="thStyleOne">Message</th>
                    <th v-if="styleNumber === 2" class="thStyleTwo">Message</th>
                    <th v-if="styleNumber === 1" class="thStyleOne">Status</th>
                    <th v-if="styleNumber === 2" class="thStyleTwo">Status</th>
                </tr>
                <tr v-if="lines?.length === 0">
                    <td colspan="4" class="p-1 font-medium italic whitespace-nowrap">No Records Found!</td>
                </tr>
                <tr v-for="line in lines1" :key="line.id" 
                    :class="{ 'bg-zinc-200': styleNumber === 1 && line.id % 6 > 2, 'bg-slate-200': styleNumber === 2 && line.id % 6 > 2 }">
                    <td class="px-1 w-48 whitespace-nowrap">{{ dayjs(line.datetime + '+00:00').format('YYYY/MM/DD HH:mm:ss') }}</td>
                    <td class="px-1 w-60 border-l" 
                        :class="{'border-zinc-500': styleNumber === 1, 'border-slate-500': styleNumber === 2}">{{ line.command }}</td>
                    <td class="px-1 w-[540px] border-l"
                        :class="{'border-zinc-500': styleNumber === 1, 'border-slate-500': styleNumber === 2}">{{ line.message }}</td>
                    <td class="px-1 w-20 border-l"
                        :class="{'border-zinc-500': styleNumber === 1, 'border-slate-500': styleNumber === 2}">{{ line.status }}</td>
                </tr>
            </table>
        </div>
        <div v-if="lines?.length >= 60">
            <div class="flex justify-center">
                <div class="mt-5 relative z-0 w-[640px] h-20 bg-lime-100 border-2 border-lime-600 rounded-md shadow-md cursor-pointer" onclick="window.open('https://owlsearch.games', '_blank')" >
                    <img src="https://owlsearch.games/images/logo/owl56.jpg" class="absolute z-10 top-[9px] left-5 mt-[1px] p-[2px] block h-14 border-2 border-emerald-950 bg-black rounded-2xl opacity-95" />
                    <span class="absolute z-10 left-28 top-[3px] text-2xl font-bold font-sans text-green-900">Owl&nbsp;Search&nbsp;Games</span>
                    <span class="absolute z-10 left-28 top-[30px] text-base font-bold font-sans text-green-800">&#10149;&nbsp;Turbospeed&nbsp;your&nbsp;Brain!</span>
                    <span class="absolute z-10 left-28 top-[47px] text-base font-bold font-sans text-green-800">&#10149;&nbsp;Play&nbsp;without&nbsp;Ads!</span>
                    <button class="absolute left-[410px] top-[17px] z-20 px-9 py-2 bg-green-600 border border-green-900 rounded-lg text-lime-200 font-bold font-sans shadow-lg" >&#10148;&nbsp;Play&nbsp;Now!</button>
                    <div class="motion-safe:animate-ping absolute z-10 left-[451px] top-[21px] w-[113px] h-[34px] bg-red-600/100 rounded-lg" />
                </div>
            </div>
            <div class="flex justify-center">
                <div class="mt-0.5 mb-4 font-black text-xs uppercase">Please support our sponsor.</div>
            </div>
        </div>
        <div v-if="lines?.length >= 60" class="flex justify-center">
            <table :class="{'bg-zinc-50 border border-zinc-500': styleNumber === 1, 'bg-slate-50 border border-slate-500': styleNumber === 2}">
                <tr v-for="line in lines2" :key="line.id" 
                    :class="{ 'bg-zinc-200': styleNumber === 1 && line.id % 6 > 2, 'bg-slate-200': styleNumber === 2 && line.id % 6 > 2 }">
                    <td class="px-1 w-48 whitespace-nowrap">{{ dayjs(line.datetime + '+00:00').format('YYYY/MM/DD HH:mm:ss') }}</td>
                    <td class="px-1 w-60 border-l" 
                        :class="{'border-zinc-500': styleNumber === 1, 'border-slate-500': styleNumber === 2}">{{ line.command }}</td>
                    <td class="px-1 w-[540px] border-l"
                        :class="{'border-zinc-500': styleNumber === 1, 'border-slate-500': styleNumber === 2}">{{ line.message }}</td>
                    <td class="px-1 w-20 border-l"
                        :class="{'border-zinc-500': styleNumber === 1, 'border-slate-500': styleNumber === 2}">{{ line.status }}</td>
                </tr>
            </table>
        </div>
        <div v-if="lines?.length >= 60">
            <div class="flex justify-center">
                <div class="mt-5 relative z-0 w-[640px] h-20 bg-lime-100 border-2 border-lime-600 rounded-md shadow-md cursor-pointer" onclick="window.open('https://owlsearch.games', '_blank')" >
                    <img src="https://owlsearch.games/images/logo/owl56.jpg" class="absolute z-10 top-[9px] left-5 mt-[1px] p-[2px] block h-14 border-2 border-emerald-950 bg-black rounded-2xl opacity-95" />
                    <span class="absolute z-10 left-28 top-[3px] text-2xl font-bold font-sans text-green-900">Owl&nbsp;Search&nbsp;Games</span>
                    <span class="absolute z-10 left-28 top-[30px] text-base font-bold font-sans text-green-800">&#10149;&nbsp;Turbospeed&nbsp;your&nbsp;Brain!</span>
                    <span class="absolute z-10 left-28 top-[47px] text-base font-bold font-sans text-green-800">&#10149;&nbsp;Play&nbsp;without&nbsp;Ads!</span>
                    <button class="absolute left-[410px] top-[17px] z-20 px-9 py-2 bg-green-600 border border-green-900 rounded-lg text-lime-200 font-bold font-sans shadow-lg" >&#10148;&nbsp;Play&nbsp;Now!</button>
                    <div class="motion-safe:animate-ping absolute z-10 left-[451px] top-[21px] w-[113px] h-[34px] bg-red-600/100 rounded-lg" />
                </div>
            </div>
            <div class="flex justify-center">
                <div class="mt-0.5 mb-4 font-black text-xs uppercase">Please support our sponsor.</div>
            </div>
        </div>
        <div v-if="lines?.length >= 60" class="flex justify-center">
            <table :class="{'bg-zinc-50 border border-zinc-500': styleNumber === 1, 'bg-slate-50 border border-slate-500': styleNumber === 2}">
                <tr v-for="line in lines3" :key="line.id" 
                    :class="{ 'bg-zinc-200': styleNumber === 1 && line.id % 6 > 2, 'bg-slate-200': styleNumber === 2 && line.id % 6 > 2 }">
                    <td class="px-1 w-48 whitespace-nowrap">{{ dayjs(line.datetime + '+00:00').format('YYYY/MM/DD HH:mm:ss') }}</td>
                    <td class="px-1 w-60 border-l" 
                        :class="{'border-zinc-500': styleNumber === 1, 'border-slate-500': styleNumber === 2}">{{ line.command }}</td>
                    <td class="px-1 w-[540px] border-l"
                        :class="{'border-zinc-500': styleNumber === 1, 'border-slate-500': styleNumber === 2}">{{ line.message }}</td>
                    <td class="px-1 w-20 border-l"
                        :class="{'border-zinc-500': styleNumber === 1, 'border-slate-500': styleNumber === 2}">{{ line.status }}</td>
                </tr>
            </table>
        </div>
        <div v-if="lines?.length < 60">
            <div class="flex justify-center">
                <div class="mt-5 relative z-0 w-[640px] h-20 bg-lime-100 border-2 border-lime-600 rounded-md shadow-md cursor-pointer" onclick="window.open('https://owlsearch.games', '_blank')" >
                    <img src="https://owlsearch.games/images/logo/owl56.jpg" class="absolute z-10 top-[9px] left-5 mt-[1px] p-[2px] block h-14 border-2 border-emerald-950 bg-black rounded-2xl opacity-95" />
                    <span class="absolute z-10 left-28 top-[3px] text-2xl font-bold font-sans text-green-900">Owl&nbsp;Search&nbsp;Games</span>
                    <span class="absolute z-10 left-28 top-[30px] text-base font-bold font-sans text-green-800">&#10149;&nbsp;Turbospeed&nbsp;your&nbsp;Brain!</span>
                    <span class="absolute z-10 left-28 top-[47px] text-base font-bold font-sans text-green-800">&#10149;&nbsp;Play&nbsp;without&nbsp;Ads!</span>
                    <button class="absolute left-[410px] top-[17px] z-20 px-9 py-2 bg-green-600 border border-green-900 rounded-lg text-lime-200 font-bold font-sans shadow-lg" >&#10148;&nbsp;Play&nbsp;Now!</button>
                    <div class="motion-safe:animate-ping absolute z-10 left-[451px] top-[21px] w-[113px] h-[34px] bg-red-600/100 rounded-lg" />
                </div>
            </div>
            <div class="flex justify-center">
                <div class="mt-0.5 font-black text-xs uppercase">Please support our sponsor.</div>
            </div>
        </div>
        <div class="h-20" />
    </div>
</template>
<style lang="postcss" scoped>
.thStyleOne {
    @apply p-1 bg-zinc-300 font-semibold border-l border-b border-zinc-500;
}
.thStyleTwo {
    @apply p-1 bg-slate-300 font-semibold border-l border-b border-slate-500;
}
</style>