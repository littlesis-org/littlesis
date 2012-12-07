<?php slot('header_text', 'Data Download') ?>

<?php slot('rightcol') ?>
<?php include_component('home', 'stats')?>
<?php end_slot() ?>

<span class="about-text">
An SQL archive of our data is available for download and updated weekly. Right click <strong><a href="https://s3.amazonaws.com/littlesis/public-databases/littlesis-data.sql">this link</a></strong> and save the file to your computer.<br />
<br />
This data download includes the following tables:<br />

<pre>
alias
business
business_industry
business_person
custom_key
degree
domain
donation
education
elected_representative
entity
extension_definition
extension_record
external_key
family
fec_filing
fedspending_filing
gender
government_body
industry
link
ls_list
ls_list_entity
membership
org
ownership
person
political_candidate
political_fundraising
political_fundraising_type
position
professional
public_company
reference
relationship
relationship_category
school
social
transaction
</pre>
</span>