<script setup>
import { ref } from 'vue';

defineProps({
    dark: { type: Boolean, default: false },
});

const pincode = ref('');
const checking = ref(false);
const result = ref(null);
const error = ref('');

// Serviceable pincodes (Bihar major cities + metros)
const serviceablePincodes = {
    // Bihar
    '800001': { city: 'Patna', days: '2-3', charge: 0 },
    '800002': { city: 'Patna', days: '2-3', charge: 0 },
    '800003': { city: 'Patna', days: '2-3', charge: 0 },
    '800004': { city: 'Patna', days: '2-3', charge: 0 },
    '800005': { city: 'Patna', days: '2-3', charge: 0 },
    '800006': { city: 'Patna', days: '2-3', charge: 0 },
    '800007': { city: 'Patna', days: '2-3', charge: 0 },
    '800008': { city: 'Patna', days: '2-3', charge: 0 },
    '800009': { city: 'Patna', days: '2-3', charge: 0 },
    '800010': { city: 'Patna', days: '2-3', charge: 0 },
    '800014': { city: 'Patna', days: '2-3', charge: 0 },
    '800020': { city: 'Patna', days: '2-3', charge: 0 },
    '800025': { city: 'Patna', days: '2-3', charge: 0 },
    '846004': { city: 'Darbhanga', days: '3-4', charge: 0 },
    '842001': { city: 'Muzaffarpur', days: '3-4', charge: 0 },
    '823001': { city: 'Gaya', days: '3-4', charge: 0 },
    '812001': { city: 'Bhagalpur', days: '3-5', charge: 0 },
    '845401': { city: 'Motihari', days: '4-5', charge: 40 },
    '854301': { city: 'Purnia', days: '4-5', charge: 40 },
    '841301': { city: 'Chapra', days: '3-4', charge: 0 },
    // NCR
    '110001': { city: 'New Delhi', days: '3-4', charge: 0 },
    '110002': { city: 'New Delhi', days: '3-4', charge: 0 },
    '110003': { city: 'New Delhi', days: '3-4', charge: 0 },
    '110005': { city: 'New Delhi', days: '3-4', charge: 0 },
    '110006': { city: 'New Delhi', days: '3-4', charge: 0 },
    '110008': { city: 'New Delhi', days: '3-4', charge: 0 },
    '110016': { city: 'New Delhi', days: '3-4', charge: 0 },
    '110017': { city: 'New Delhi', days: '3-4', charge: 0 },
    '110019': { city: 'New Delhi', days: '3-4', charge: 0 },
    '110020': { city: 'New Delhi', days: '3-4', charge: 0 },
    '110025': { city: 'New Delhi', days: '3-4', charge: 0 },
    '110030': { city: 'New Delhi', days: '3-4', charge: 0 },
    '110044': { city: 'New Delhi', days: '3-4', charge: 0 },
    '110048': { city: 'New Delhi', days: '3-4', charge: 0 },
    '110065': { city: 'New Delhi', days: '3-4', charge: 0 },
    '110085': { city: 'New Delhi', days: '3-4', charge: 0 },
    '110092': { city: 'New Delhi', days: '3-4', charge: 0 },
    '201301': { city: 'Noida', days: '3-4', charge: 0 },
    '122001': { city: 'Gurgaon', days: '3-4', charge: 0 },
    // Mumbai
    '400001': { city: 'Mumbai', days: '4-5', charge: 0 },
    '400050': { city: 'Mumbai', days: '4-5', charge: 0 },
    '400053': { city: 'Mumbai', days: '4-5', charge: 0 },
    '400069': { city: 'Mumbai', days: '4-5', charge: 0 },
    '400076': { city: 'Mumbai', days: '4-5', charge: 0 },
    // Bangalore
    '560001': { city: 'Bangalore', days: '4-6', charge: 0 },
    '560034': { city: 'Bangalore', days: '4-6', charge: 0 },
    '560100': { city: 'Bangalore', days: '4-6', charge: 0 },
    // Kolkata
    '700001': { city: 'Kolkata', days: '3-4', charge: 0 },
    '700020': { city: 'Kolkata', days: '3-4', charge: 0 },
    // Hyderabad
    '500001': { city: 'Hyderabad', days: '4-5', charge: 0 },
    // Chennai
    '600001': { city: 'Chennai', days: '5-6', charge: 0 },
    // Pune
    '411001': { city: 'Pune', days: '4-5', charge: 0 },
    // Lucknow
    '226001': { city: 'Lucknow', days: '3-4', charge: 0 },
    // Jaipur
    '302001': { city: 'Jaipur', days: '4-5', charge: 0 },
    // Ranchi
    '834001': { city: 'Ranchi', days: '3-4', charge: 0 },
};

function checkAvailability() {
    const cleaned = pincode.value.trim();
    error.value = '';
    result.value = null;

    if (!/^\d{6}$/.test(cleaned)) {
        error.value = 'Please enter a valid 6-digit pincode';
        return;
    }

    checking.value = true;

    // Simulate network delay
    setTimeout(() => {
        const match = serviceablePincodes[cleaned];
        if (match) {
            result.value = {
                available: true,
                city: match.city,
                days: match.days,
                charge: match.charge,
            };
        } else {
            // Check if pincode prefix matches any known area
            const prefix = cleaned.substring(0, 2);
            const biharPrefixes = ['80', '81', '82', '83', '84', '85', '86'];
            if (biharPrefixes.includes(prefix)) {
                result.value = {
                    available: true,
                    city: 'Bihar',
                    days: '4-6',
                    charge: 40,
                };
            } else if (['11', '12', '13', '20', '21'].includes(prefix)) {
                result.value = {
                    available: true,
                    city: 'North India',
                    days: '4-6',
                    charge: 49,
                };
            } else if (parseInt(cleaned) >= 100000 && parseInt(cleaned) <= 999999) {
                result.value = {
                    available: true,
                    city: 'India',
                    days: '5-7',
                    charge: 69,
                };
            } else {
                result.value = { available: false };
            }
        }
        checking.value = false;
    }, 500);
}
</script>

<template>
    <div class="space-y-2">
        <label :class="dark ? 'text-amber-200' : 'text-gray-700'" class="text-sm font-medium flex items-center gap-2">
            <svg :class="dark ? 'text-amber-300' : 'text-gray-500'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Check Delivery Availability
        </label>
        <div class="flex gap-2">
            <input
                v-model="pincode"
                type="text"
                maxlength="6"
                inputmode="numeric"
                placeholder="Enter pincode"
                :class="dark ? 'bg-white/10 border-amber-400/30 text-white placeholder-amber-300/50 focus:border-amber-400 focus:ring-amber-400' : 'border-gray-200 focus:border-amber-500 focus:ring-amber-500'"
                class="flex-1 px-3 py-2 text-sm border rounded-lg"
                @keyup.enter="checkAvailability"
            />
            <button
                @click="checkAvailability"
                :disabled="checking"
                class="px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition disabled:opacity-50 shrink-0"
            >
                <span v-if="checking" class="flex items-center gap-1">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </span>
                <span v-else>Check</span>
            </button>
        </div>

        <!-- Error -->
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <!-- Available -->
        <div v-if="result?.available" class="bg-green-50 border border-green-200 rounded-lg p-3">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-green-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <div class="text-sm">
                    <p class="text-green-800 font-medium">Delivery available to {{ result.city }}!</p>
                    <p class="text-green-700 mt-0.5">
                        Estimated delivery: <span class="font-semibold">{{ result.days }} business days</span>
                    </p>
                    <p class="text-green-700">
                        <span v-if="result.charge === 0" class="font-semibold text-green-800">FREE Delivery</span>
                        <span v-else>Delivery charge: <span class="font-semibold">₹{{ result.charge }}</span> <span class="text-green-600">(Free above ₹499)</span></span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Not available -->
        <div v-if="result && !result.available" class="bg-red-50 border border-red-200 rounded-lg p-3">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <div class="text-sm">
                    <p class="text-red-700 font-medium">Delivery not available</p>
                    <p class="text-red-600 mt-0.5">Sorry, we don't deliver to this pincode yet. We're expanding fast — check back soon!</p>
                </div>
            </div>
        </div>
    </div>
</template>
