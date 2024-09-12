<template>
    <div class="max-w-xl mx-auto rounded shadow bg-white">
        <div class="max-w-lg mx-auto pt-6 relative">
            <div class="wizard-steps">
                <a class="step" :class="{'complete': currentStep >= index}" v-for="(step, index) in steps" @click="goToStep(index)">
                    <div class="ball">{{ index+1 }}</div>
                    <div class="label">{{ step }}</div>
                </a>
            </div>
        </div>

        <!-- Step 1 -->
        <div v-if="currentStep === 0">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Create a New Experiment') }}</h1>
                <p class="text-grey" v-text="__('Experiments allow you to run variations on parts of your website and track the visitors\' experiences, to make data-driven decisions on changes.')" />
            </div>

            <!-- Name -->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Title') }}</label>
                <input type="text" v-model="experiment.title" class="input-text" autofocus tabindex="1">
            </div>

            <!-- Handle -->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Handle') }}</label>
                <input type="text" v-model="experiment.handle" class="input-text" tabindex="2">
            </div>
        </div>

        <!-- Step 2 -->
        <div v-if="currentStep === 1">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Variants') }}</h1>
                <p class="text-grey" v-text="__('Variants are the names of each of the experiment variations you wish to create.')" />
            </div>

            <!-- Fields -->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Variants') }}</label>
                <list-fieldtype class="list-reset" v-model="experiment.variants" />
            </div>
        </div>

        <!-- Step 3 -->
        <div v-if="currentStep === 2">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Goal') }}</h1>
                <p class="text-grey" v-text="__('A goal is the \'thing\' that defines whether an experiment was successful.')" />
            </div>

            <!-- Name -->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Goal Type') }}</label>
                <select-input v-model="experiment.goal_type" :options="[{ value: 'redirect', label: 'Redirect' }]" />
            </div>

            <!-- Email-->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Goal Destination') }}</label>
                <input type="text" v-model="experiment.goal_destination" class="input-text" autofocus tabindex="1">
            </div>
        </div>

        <div class="border-t p-2">
            <div class="max-w-md mx-auto flex items-center justify-center">
                <button tabindex="3" class="btn mx-2 w-32" @click="previous" v-if="! onFirstStep">
                    &larr; {{ __('Previous')}}
                </button>
                <button tabindex="4" class="btn mx-2 w-32" :disabled="! canContinue" @click="next" v-if="! onLastStep">
                    {{ __('Next')}} &rarr;
                </button>
                <button tabindex="4" class="btn-primary mx-3" :disabled="! canSubmit" @click="submit" v-if="onLastStep">
                    {{ __('Create Experiment') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import HasWizardSteps from './HasWizardSteps.js';

export default {
  mixins: [HasWizardSteps],

  props: {
    route: {
      type: String
    }
  },

  data() {
    return {
      currentStep: 0,
      steps: [__('Naming'), __('Variants'), __('Goal')],
      experiment: {
        title: null,
        handle: null,
        variants: [],
        goal_type: null,
        goal_destination: null,
      },
    }
  },

  computed: {
    canSubmit() {
      if (this.experiment.email) {
        return isEmail(this.experiment.email);
      }

      return true;
    }
  },

  methods: {
    canGoToStep(step) {
      if (step >= 1) {
        return Boolean(this.experiment.title && this.experiment.handle);
      }

      return true;
    },

    submit() {
      this.$axios.post(this.route, this.experiment).then(response => {
        window.location = response.data.redirect;
      }).catch(error => {
        this.$toast.error(error.response.data.message);
      });
    }
  },

  watch: {
    'experiment.title'(val) {
      this.experiment.handle = this.$slugify(val, '_');
    },
  },

  mounted() {
    this.$keys.bindGlobal(['command+return'], this.next);
    this.$keys.bindGlobal(['command+delete'], this.previous);
  }
}
</script>
