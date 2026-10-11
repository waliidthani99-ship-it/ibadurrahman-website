<?php
/**
 * Shared student form used by add_student.php and edit_student.php.
 *
 * Expected variables before include:
 *   $formAction  - POST target (e.g. "add_student.php")
 *   $studentData - associative array of current values (may be empty)
 *   $submitLabel - text for the submit button
 *   $showStatus  - bool, render status selector (edit mode)
 *   $formErrors  - array of error strings
 */

$formAction  = isset($formAction) ? $formAction : "add_student.php";
$studentData = isset($studentData) && is_array($studentData) ? $studentData : [];
$submitLabel = isset($submitLabel) ? $submitLabel : "Save Student";
$showStatus  = !empty($showStatus);
$formErrors  = isset($formErrors) && is_array($formErrors) ? $formErrors : [];

$fv = function ($key, $default = "") use ($studentData) {
    return array_key_exists($key, $studentData) && $studentData[$key] !== null ? (string)$studentData[$key] : $default;
};

$inputClass = "w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary focus:bg-white transition-all";
$labelClass = "block text-[13px] font-semibold text-gray-600 mb-1.5";
$upperStyle = ' style="text-transform:uppercase"';
?>
<form method="POST" action="<?php echo sanitize($formAction); ?>" class="space-y-6">
  <?php csrf_field(); ?>

  <?php if ($showStatus && $fv("id") !== ""): ?>
    <input type="hidden" name="id" value="<?php echo (int)$fv("id"); ?>">
  <?php endif; ?>

  <?php if ($formErrors): ?>
    <div class="flex items-start gap-3 rounded-xl px-4 py-3 text-sm font-medium bg-rose-50 text-rose-700 ring-1 ring-rose-200">
      <i class="fas fa-circle-exclamation mt-0.5"></i>
      <ul class="list-disc list-inside space-y-0.5">
        <?php foreach ($formErrors as $e): ?><li><?php echo sanitize($e); ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- Student information -->
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 overflow-hidden">
    <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
      <span class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center"><i class="fas fa-user-graduate"></i></span>
      <h2 class="font-bold text-gray-800">Student Information</h2>
    </div>
    <div class="p-6 grid sm:grid-cols-2 gap-5">
      <div class="sm:col-span-2">
        <label class="<?php echo $labelClass; ?>">Full Name <span class="text-rose-500">*</span></label>
        <input type="text" name="full_name" required value="<?php echo sanitize($fv("full_name")); ?>" placeholder="e.g. KOMBO JECHA KILOLO" class="<?php echo $inputClass; ?>"<?php echo $upperStyle; ?>>
      </div>

      <div>
        <label class="<?php echo $labelClass; ?>">Date of Birth</label>
        <input type="date" name="date_of_birth" value="<?php echo sanitize($fv("date_of_birth")); ?>" class="<?php echo $inputClass; ?>">
        <p class="text-[11px] text-gray-400 mt-1">Optional — leave blank if unknown.</p>
      </div>

      <div>
        <label class="<?php echo $labelClass; ?>">Gender</label>
        <select name="gender" class="<?php echo $inputClass; ?>">
          <option value="">Select...</option>
          <?php foreach (["Male", "Female"] as $g): ?>
            <option value="<?php echo $g; ?>" <?php if ($fv("gender") === $g) echo "selected"; ?>><?php echo $g; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="<?php echo $labelClass; ?>">Current Class</label>
        <input type="text" name="current_class" value="<?php echo sanitize($fv("current_class")); ?>" placeholder="e.g. Darasa la Tano / Form One" class="<?php echo $inputClass; ?>">
      </div>

      <div>
        <label class="<?php echo $labelClass; ?>">Admission Year</label>
        <input type="number" name="admission_year" id="admissionYearInput" min="1900" max="2100" placeholder="e.g. 2019"
               value="<?php echo sanitize($fv("admission_year")); ?>" class="<?php echo $inputClass; ?>">
        <label class="inline-flex items-center gap-2 mt-2 text-[13px] text-gray-500 cursor-pointer">
          <input type="checkbox" name="admission_year_unknown" id="admissionUnknown" value="1" class="rounded border-slate-300 text-primary focus:ring-primary/40"
                 <?php if ($fv("admission_year") === "") echo "checked"; ?>>
          Admission year unknown
        </label>
      </div>
  </div>

  <!-- Parent / guardian -->
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 overflow-hidden">
    <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
      <span class="w-9 h-9 rounded-xl bg-accent/10 text-accent flex items-center justify-center"><i class="fas fa-user-shield"></i></span>
      <h2 class="font-bold text-gray-800">Parent / Guardian</h2>
    </div>
    <div class="p-6 grid sm:grid-cols-2 gap-x-6 gap-y-5">
      <div>
        <label class="<?php echo $labelClass; ?>">Full Name <span class="text-rose-500">*</span></label>
        <input type="text" name="parent_name" value="<?php echo sanitize($fv("parent_name")); ?>" required class="<?php echo $inputClass; ?>"<?php echo $upperStyle; ?>>
      </div>
      <div>
        <label class="<?php echo $labelClass; ?>">Phone Number <span class="text-rose-500">*</span></label>
        <input type="text" name="parent_phone" value="<?php echo sanitize($fv("parent_phone")); ?>" required class="<?php echo $inputClass; ?>">
      </div>
      <div class="sm:col-span-2">
        <label class="<?php echo $labelClass; ?>">Email</label>
        <input type="email" name="parent_email" value="<?php echo sanitize($fv("parent_email")); ?>" class="<?php echo $inputClass; ?>">
      </div>
    </div>
  </div>

  <!-- Additional details -->
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 overflow-hidden">
    <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
      <span class="w-9 h-9 rounded-xl bg-slate-100 text-gray-500 flex items-center justify-center"><i class="fas fa-circle-info"></i></span>
      <h2 class="font-bold text-gray-800">Additional Details</h2>
    </div>
    <div class="p-6 grid sm:grid-cols-2 gap-x-6 gap-y-5">
      <div class="sm:col-span-2">
        <label class="<?php echo $labelClass; ?>">Address <span class="text-rose-500">*</span></label>
        <textarea name="address" rows="2" required class="<?php echo $inputClass; ?>"<?php echo $upperStyle; ?>><?php echo sanitize($fv("address")); ?></textarea>
      </div>
      <div>
        <label class="<?php echo $labelClass; ?>">School</label>
        <input type="text" name="school" value="<?php echo sanitize($fv("school")); ?>" class="<?php echo $inputClass; ?>"<?php echo $upperStyle; ?>>
      </div>
      <div>
        <label class="<?php echo $labelClass; ?>">Madrasa</label>
        <input type="text" name="madrasa" value="<?php echo sanitize($fv("madrasa")); ?>" class="<?php echo $inputClass; ?>"<?php echo $upperStyle; ?>>
      </div>
      <div>
        <label class="<?php echo $labelClass; ?>">School Leaving Time</label>
        <input type="text" name="school_leaving_time" value="<?php echo sanitize($fv("school_leaving_time")); ?>" class="<?php echo $inputClass; ?>">
      </div>
      <?php if ($showStatus): ?>
        <div>
          <label class="<?php echo $labelClass; ?>">Status</label>
          <select name="status" class="<?php echo $inputClass; ?>">
            <?php foreach (["Active", "Approved", "Pending", "Rejected"] as $st): ?>
              <option value="<?php echo $st; ?>" <?php if ($fv("status") === $st) echo "selected"; ?>><?php echo $st; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>
      <div class="sm:col-span-2">
        <label class="<?php echo $labelClass; ?>">Notes</label>
        <textarea name="notes" rows="3" class="<?php echo $inputClass; ?>"><?php echo sanitize($fv("notes")); ?></textarea>
      </div>
    </div>
  </div>

  <div class="flex flex-wrap items-center justify-end gap-3">
    <a href="registrations.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold hover:bg-slate-200 transition-colors">
      Cancel
    </a>
    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-darkGreen transition-colors shadow-sm">
      <i class="fas fa-floppy-disk"></i><span><?php echo sanitize($submitLabel); ?></span>
    </button>
  </div>
</form>

<script>
(function () {
  var chk = document.getElementById("admissionYearInput") ? document.getElementById("admissionUnknown") : null;
  var input = document.getElementById("admissionYearInput");
  if (chk && input) {
    var sync = function () { input.disabled = chk.checked; if (chk.checked) input.value = ""; };
    chk.addEventListener("change", sync);
    sync();
  }
})();
</script>
