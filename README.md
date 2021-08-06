# Technical Assessment - Pop's Tops Store

**Note:** *I was not entirely sure what folder to push to the repo, but I assumed that I would need the theme and and plugin in there, so I added the entire wp-content folder for good measure. Additionally, I was not sure how to push the database, so I did two things: I pushed the data from the database used in mysql, and I exported the tables from the database into an SQL file for good measure.*

To see just the **functions.php** file, I have put the link here: https://github.com/afuamensah/techassessment/blob/main/themes/twentytwentyone-child/functions.php

<br/>

## My Process

First, after downloading the Wordpress zipped folder, I extracted it and moved it to the htdocs folder in XAMPP.

I then created a database using PHPMYAdmin called techtest.

After completing the starting steps, I created a child theme of the parent Twenty Twenty-One theme. Here are the steps I took to do it.
1. I created a folder called "twentytwentyone-child" in the wp-content/themes folder.
2. In the folder I created a style.css file and added a header comment.
3. Then, in the same folder, I created a functions.php file and enqueued the theme.
4. After that, I went back to the theme page in Wordpress and found the child theme.
5. Upon finding it I activated the theme to the site.

I then installed the Woocommerce plugin. Right after, I went to the Woocommerce attributes page and created two attributes. Since my idea for the store was to have laptops as products to sell, I decided that my two attributes for the laptops were the **brand** of the laptop and the **OS (Operating System)** that the laptop comes with. After putting in some examples for brands and operating system, I added one laptop as a product to start. I added the attribute for brand and OS (in the first product's case, it is Dell as the brand and Ubuntu Linux as the OS), and published the product.

Now, for creating code so the attributes will display under a product on the shop page, I decided to look at the WooCommerce code reference page since I was not well-versed in Woocommerce and its functions yet. 

After some research, here is how I figured it out and put the code in the child theme's functions.php file:
1. I first called the ```add_action``` function. 
2. In the parameters, I put where I wanted the attributes to be displayed under, which is under the price, and I put the name of the function that will detail how the attributes will be shown. It is called ```show_attributes```.
3. I created the ```show_attributes``` function, and in it, I declared the global variable called ```$product```, so I am able to get its attributes.
4. I then passed the varible through the ```get_attributes()``` function, which returns an associative array of key-value pairs. 
5. I then did a foreach loop, which runs through each pair in the array.
6. In the foreach loop, I put the product through the ```get_attribute()``` function and put $x, which represents the taxonomy name, as the parameter. This returns the name of the attribute (ie. Dell).
7. I also used the function ```wc_attribute_label()``` with $x (the taxonomy name) as the parameter so I could get the attribute's category/label (ie. Brand). 
8. Lastly, I echoed the statement with the label in bold and attribute next to it.

After testing it, I added another product to make sure it would work with that product and it did. To conclude, this was a bit of a challenge, but with using the WooCommerce code reference pages, it was manageable.
