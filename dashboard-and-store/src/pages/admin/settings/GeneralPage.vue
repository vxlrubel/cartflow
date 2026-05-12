<template>
<div>
  <PageTitle title="General Settings"/>

  <div class="space-y-6 lg:space-y-8 text-gray-800">

    <SettingsOption label="Store Name" hint="The name of your store that will be displayed to customers">
      <input type="text" class="input-field bg-white md:max-w-100" placeholder="Caltflow">
    </SettingsOption>

    <SettingsOption label="Tagline" subtitle="A short description of your store" hint="In a few words, explain what this site is about. Example: “Just another store site.”">
      <input type="text" class="input-field bg-white md:max-w-100" placeholder="My Awesome Store">
    </SettingsOption>

    <SettingsOption label="Site Icon" hint="The Site Icon is what you see in browser tabs, bookmark bars, and within the mobile apps. It should be square and at least 512 by 512 pixels.">
      <template v-if="siteIcon">

        <div class="max-w-100 pl-3 pt-3 border border-gray-200 bg-white flex gap-4">
          <div class="w-15 h-15 rounded-2xl border overflow-hidden">
            <img :src="siteIcon" alt="Site Icon" class="w-full h-full object-cover">
          </div>
          <div class="rounded-tl-2xl pt-1 pl-3 bg-gray-900 h-18 flex-1 flex justify-between items-start gap-2">
            <div>
              <span class="inline-flex rounded-full h-3 w-3 bg-[#8C8F94]"></span>
              <span class="inline-flex rounded-full h-3 w-3 bg-[#8C8F94] mx-1"></span>
              <span class="inline-flex rounded-full h-3 w-3 bg-[#8C8F94]"></span>
            </div>
            <div class="flex items-center flex-1">
              <div class="w-7 h-7 rounded border overflow-hidden">
                <img :src="siteIcon" alt="Site Icon" class="w-full h-full object-cover">
              </div>
              <span class="text- text-white ml-1">Caltflow</span>
            </div>
            <button class="text-white hover:text-theme-400 h-7 w-7 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#e3e3e3">
                <path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="flex items-center gap-2 mt-2">
          <button type="button" class="text-xs font-medium py-1.5 cursor-pointer text-gray-500 transition-colors duration-200 hover:text-theme-400 hover:border-theme-200 px-4 border-2 border-gray-300" @click="chooseSiteIcon">Change Site Icon</button>
          <button type="button" class="text-xs font-medium py-1.5 cursor-pointer text-gray-500 transition-colors duration-200 hover:text-theme-400 hover:border-theme-200 px-4 border-2 border-gray-300" @click="siteIcon = null">Remove Site Icon</button>
        </div>

      </template>

      <button v-else class="inline-flex items-center w-60 border border-gray-200 justify-center h-15 bg-white cursor-pointer hover:text-theme-500 transition-colors duration-200" @click="chooseSiteIcon">Choose a Site Icon</button>
    </SettingsOption>

    <SettingsOption label="Site Address (URL)">
      <input type="url" class="input-field bg-white md:max-w-100" placeholder="https://example.com">
    </SettingsOption>

    <SettingsOption label="Administration Email Address" hint="This address is used for admin purposes. If you change this, an email will be sent to your new address to confirm it. The new address will not become active until confirmed.">
      <input type="email" class="input-field bg-white md:max-w-100" placeholder="admin@example.com">
    </SettingsOption>

    <SettingsOption label="Site Language">
      <CustomSelect class="max-w-100" :options="[
        { label: 'English (United States)', value: 'en-US' },
        { label: 'Spanish (Spain)', value: 'es-ES' },
        { label: 'French (France)', value: 'fr-FR' },
        { label: 'German (Germany)', value: 'de-DE' },
        { label: 'Chinese (Simplified)', value: 'zh-CN' },
        { label: 'Japanese (Japan)', value: 'ja-JP' },
        { label: 'Portuguese (Brazil)', value: 'pt-BR' },
        { label: 'Russian (Russia)', value: 'ru-RU' },
        { label: 'Italian (Italy)', value: 'it-IT' },
        { label: 'Korean (South Korea)', value: 'ko-KR' }
      ]" v-model="language" placeholder="Select Site Language"/>
    </SettingsOption>
    <SettingsOption label="Timezone" hint="Choose either a city in the same timezone as you or a UTC (Coordinated Universal Time) time offset.">
      <CustomSelect class="max-w-100" :options="[
        { label: 'UTC', value: 'UTC' },
        { label: 'America/New_York', value: 'America/New_York' },
        { label: 'America/Los_Angeles', value: 'America/Los_Angeles' },
        { label: 'Europe/London', value: 'Europe/London' },
        { label: 'Asia/Tokyo', value: 'Asia/Tokyo' }
      ]" v-model="timezone" placeholder="Select Timezone"/>
    </SettingsOption>

    <SettingsOption label="Week Starts On">
      <CustomSelect v-model="weekStart" class="max-w-50" :options="[
        { label: 'Sunday', value: 'sunday' },
        { label: 'Monday', value: 'monday' },
        { label: 'Tuesday', value: 'tuesday' },
        { label: 'Wednesday', value: 'wednesday' },
        { label: 'Thursday', value: 'thursday' },
        { label: 'Friday', value: 'friday' },
        { label: 'Saturday', value: 'saturday' }
      ]"/>
    </SettingsOption>

    <SettingsOption label="Date Format" :hint="'Preview: ' + selectedDate">
      <div class="max-w-75 text-sm">
        <DateFormatPicker v-model="selectedDate" />
      </div>
    </SettingsOption>

    <SettingsOption label="Time Format" :hint="'Preview: ' + selectedTime">
      <div class="max-w-75 text-sm">
         <TimeFormatPicker v-model="selectedTime" />
      </div>
    </SettingsOption>

    <div class="pt-4 pb-15 border-t border-gray-300">
      <PrimacyButton label="Save Changes"/>
    </div>



  </div>


</div>
</template>

<script setup>
import { ref } from 'vue'
import PageTitle from '@/components/admin/PageTitle.vue'
import SettingsOption from '@/components/admin/SettingsOption.vue'
import { openMediaBox } from '@/services/media-box'
import CustomSelect from '@/components/CustomSelect.vue'
import DateFormatPicker from '@/components/DateFormatPicker.vue'
import TimeFormatPicker from '@/components/TimeFormatPicker.vue'
import PrimacyButton from '../../../components/buttons/PrimacyButton.vue'

const selectedDate = ref('')
const selectedTime = ref('')


const siteIcon = ref(null)

const chooseSiteIcon = async () => {
  const media = await openMediaBox()
  siteIcon.value = media.url
}

const language = ref('en-US')
const timezone = ref('UTC')
const weekStart = ref('sunday')

</script>
