# Contact Form Testing Checklist

## Quick Test URLs

- **Test Form:** http://localhost/LandPage/test-contact-form.html
- **SMTP Test:** http://localhost/LandPage/test-email.php
- **Verification Dashboard:** http://localhost/LandPage/verify-form.html

## Test Scenarios

### ✅ Test 1: Valid Form Submission

1. Open test form: http://localhost/LandPage/test-contact-form.html
2. Fill in all fields:
   - First Name: John
   - Last Name: Doe
   - Email: test@example.com
   - Phone: +32 123 456 789
   - Subject: Select "Demo Request"
   - Message: "I would like to request a demo of your POS system for my restaurant."
3. Check the GDPR consent checkbox
4. Submit the form
5. **Expected Result:**
   - Loading message appears: "Sending... Please wait while we process your request"
   - Success message appears: "Message Sent! Thank you for your message!"
   - Form resets after clicking OK

### ✅ Test 2: Missing Required Fields

1. Open test form
2. Leave First Name empty
3. Try to submit
4. **Expected Result:**
   - Error message: "First name is required"
   - NO loading message should appear
   - Form should NOT be submitted

### ✅ Test 3: GDPR Consent Not Checked

1. Open test form
2. Fill all fields BUT don't check GDPR consent
3. Try to submit
4. **Expected Result:**
   - Error message: "Please accept the privacy policy and terms of service"
   - NO loading message should appear

### ✅ Test 4: Invalid Email Format

1. Open test form
2. Enter invalid email: "notanemail"
3. Fill other fields correctly
4. Try to submit
5. **Expected Result:**
   - Error message: "Please enter a valid email address"

### ✅ Test 5: Message Too Short

1. Open test form
2. Fill all fields
3. Enter message with less than 10 characters: "Test"
4. Try to submit
5. **Expected Result:**
   - Error message: "Please provide a more detailed message (at least 10 characters)"

### ✅ Test 6: Character Counter

1. Open test form
2. Type in the message field
3. **Expected Result:**
   - Character counter shows: "X/2000 characters"
   - Counter changes color at 1600 chars (yellow)
   - Counter changes color at 2000+ chars (red)

### ✅ Test 7: Rate Limiting

1. Open test form and submit successfully
2. Immediately try to submit again
3. **Expected Result:**
   - Message: "Please wait X seconds before submitting again"
   - Countdown timer shows remaining seconds
   - Timer updates every second
   - Form cannot be submitted until timer expires

### ✅ Test 8: Rate Limit Persistence

1. Submit form successfully
2. Refresh the page
3. Try to submit again
4. **Expected Result:**
   - Rate limit still active (timer doesn't reset on refresh)
   - Shows remaining time from original submission
   - Timer continues counting down from where it left off

## Email Testing

### ✅ Test 9: SMTP Connection

1. Open: http://localhost/LandPage/test-email.php
2. **Expected Result:**
   - Shows current configuration
   - "SMTP Connection Successful!" message
   - Test email sent to contact@fastcaisse.be

### ✅ Test 10: Admin Email Receipt

1. After successful form submission
2. Check contact@fastcaisse.be inbox
3. **Expected Result:**
   - Email subject: "Nouveau message de contact - FastCaisse"
   - Email contains all submitted information
   - Well-formatted HTML email

### ✅ Test 11: Client Confirmation Email

1. Submit form with your email
2. Check your inbox
3. **Expected Result:**
   - Confirmation email received
   - Subject: "Merci pour votre message - FastCaisse"
   - Professional confirmation message

## Language Testing

### ✅ Test 12: French Version

1. Open: http://localhost/LandPage/home.html
2. Navigate to contact form section
3. Submit form
4. **Expected Result:**
   - All messages in French
   - "Envoi en cours..." loading message
   - "Message envoyé !" success message

### ✅ Test 13: English Version

1. Open: http://localhost/LandPage/home-en.html
2. Navigate to contact form section
3. Submit form
4. **Expected Result:**
   - All messages in English
   - "Sending..." loading message
   - "Message Sent!" success message

### ✅ Test 14: Dutch Version

1. Open: http://localhost/LandPage/home-nl.html
2. Navigate to contact form section
3. Submit form
4. **Expected Result:**
   - All messages in Dutch
   - "Verzenden..." loading message
   - "Bericht Verzonden!" success message

## Browser Console Checks

### ✅ Test 15: Console Debugging

1. Open browser Developer Tools (F12)
2. Go to Console tab
3. Submit a form
4. **Expected Result:**
   - "Form data collected: {object}" log
   - "Validation passed, proceeding to submit..." log
   - No JavaScript errors

## Common Issues Resolution

### Issue: "Oops... Please fill out all required fields" appears even when all fields are filled

**Solution:** This has been fixed. The validation now happens before showing any loading message.

### Issue: Email not sending but form shows success

**Solution:** Check SMTP test page (test-email.php) to verify SMTP configuration.

### Issue: Form submits but no confirmation email

**Solution:**

1. Check spam folder
2. Verify email address is correct
3. Check PHP error logs

## Final Verification

Once all tests pass:

1. ✅ Form validation works correctly
2. ✅ Loading message appears only after validation
3. ✅ Success/error messages display properly
4. ✅ Emails are sent to admin (contact@fastcaisse.be)
5. ✅ Confirmation emails are sent to users
6. ✅ Form works in all languages (FR, EN, NL)
7. ✅ No JavaScript errors in console
8. ✅ Character counter works
9. ✅ GDPR consent is required
10. ✅ Form resets after successful submission

## Production Deployment

Before going live:

1. Update SMTP credentials if different for production
2. Test on actual production server
3. Remove test files
4. Monitor first 10 submissions
5. Check email delivery rates
