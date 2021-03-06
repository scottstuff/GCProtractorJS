<?php

namespace GotChosen\SiteBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use GotChosen\SiteBundle\Entity\NewsArticle;
use GotChosen\SiteBundle\Entity\NewsArticleContent;
use GotChosen\SiteBundle\Entity\NewsCategory;


class LoadNewsArticles implements FixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    function load(ObjectManager $manager)
    {
        $catRepo = $manager->getRepository('GotChosenSiteBundle:NewsCategory');

        foreach ( array ('winners' => 'Winners', 'latest' => 'Latest News') as $shortName => $name ) {
            if ( !$catRepo->findOneBy(['shortName' => $shortName]) ) {
                $o = new NewsCategory();
                $o->setName($name);
                $o->setShortName($shortName);
                $manager->persist($o);
            }
            $manager->flush();
        }

        $news = array(
                    array(
                        'title'        => 'GotChosen Congratulates the Fourth GotScholarship $40K Winner',
                        'category'     => 'winners',
                        'date'         => '5/8/2014',
                        'published'    => TRUE,
                        'content'      => '
                            <p>
                                Nelson Mandela said, "Education is the most powerful weapon we can use to change the world."  At GotChosen,
                                we are focused on change. Mr. Mandela\'s observation of the influence of education is a testimony to the
                                importance of the GotScholarship programs. The recipients of our scholarships will be the change for our future.
                            </p>

                            <p>
                                <img src="https://s3.amazonaws.com/GCNewsArticles/Oz_Fran.jpg" alt=""
                                     style="float:left; margin:0 8px 8px 8px;width:320px;" />
                                <a href="http://www.incubator.ucf.edu" target="_blank">University of Central Florida\'s Business Incubation Program</a>
                                hosted the GotScholarship drawing. At UCF, Dr. Tom O\'Neal is the Management Executive Director. Using Random.org, Dr.
                                O\'Neal drew our fourth $40,000 winner. Fran Korosec, Director of Programs for the UCF Incubation Program, coordinated
                                the event. Both Dr. O\'Neal and Mr. Korosec have a long history of supporting the dreams of start-ups in Central Florida.
                                Their partnership with GotChosen came from the drive of sharing of similar goals.
                            </p>

                            <p>
                                And the winner is&hellip; Kellie Ventura, a junior at Humboldt State University in Arcata CA. Kellie is studying wildlife
                                conservation and management. Kellie has a story that many can relate to and hopefully learn from. Kellie told us "I heard
                                about the GotChosen scholarship through <a href="http://www.fastweb.com" target="_blank">Fastweb.com</a>. I saw how easy
                                the application was and thought, <em>why not?</em> I sent around the link to my family and friends but none of us honestly believed
                                that I would win."
                            </p>

                            <p>
                                <img src="https://s3.amazonaws.com/GCNewsArticles/Kellie.jpg" alt=""
                                     style="float:right; margin:0 8px 20px 8px;width:185px;" />

                                "I had tried really hard for a similar scholarship the year before," Kelly said.  "We were all so disappointed when I lost
                                that we didn’t really get our hopes up for this one. Suffice to say that I nearly lost my mind from happiness when I found
                                out I had won!"
                            </p>

                            <p>
                                "It was one of the best days of my life," she continued. "Right alongside the day I was accepted into college. I am so
                                thankful I found this scholarship".
                            </p>

                            <p>
                                Kellie, with her family\'s support, began college without having all the funds to pay for it. She has worked hard every year
                                and each summer to help pay the bills and has also taken on student debt. And every year, Kellie searched for new ways to
                                cover her college. She has a "never give up" attitude. We are excited to follow Kellie as she finishes her degree and uses
                                the scholarship to pay down her student loans.
                            </p>
                        ',
                    ),
                    array(
                        'title'        => 'GotChosen Congratulates the March $1000 Every Month Scholarship Winner',
                        'category'     => 'winners',
                        'date'         => '4/24/2014',
                        'published'    => TRUE,
                        'content'      => '
                        <div>
                            <img src="/bundles/gotchosensite/images/drawing.jpg" alt="" style="float:right; margin:0 8px 8px 8px;width:320px;">

                            <p>
                                The <a href="http://www.incubator.ucf.edu/" target="_blank">UCF Business Incubation Program</a>, a University-driven community association that provides upstarts
                                with the tools, training and support, hosted our March Every Month Scholarship drawing.
                            </p>
                            <p>GotChosen\'s commitment to support others was perfectly matched with the mission of the Incubation Program.
                                Dr. Tom O\'Neal, Management Executive Director chose the randomly drawn winner, Caitlin Duchene.
                            </p>
                            <p>
                                Caitlin told us she was "beyond thrilled to have been drawn for the GotChosen $1000 Every Month Scholarship.
                            </p>
                        </div>

                        <div style="clear:both;"></div>

                        <div>

                            <img src="/bundles/gotchosensite/images/Caitlin_gotchosen.jpg" alt="" style="float:left; margin:0 8px 8px 0;">

                            <p>
                                I found out about this scholarship through my study abroad program IES Abroad. Receiving
                                these funds is very exciting as I am studying abroad in Barcelona, Spain this summer and they will
                                greatly contribute to my program fees. Thank you GotChosen for helping me have a once in a lifetime
                                summer experience".  Caitlin is a student at <a href="https://www.utexas.edu/" target="_blank">University of Texas, Austin</a>. She is studying Government
                                and is looking forward to her summer program to be able to compare different governmental policies
                                and procedures.
                            </p>

                            <p>
                                GotChosen is always excited to support the educational goals for students. We are confident that
                                Caitlin will represent the University of Texas and her country, with pride and professionalism. We
                                hope Caitlin will experience the distinctiveness of Barcelona as well as the uniqueness of humanity
                                that unites us all.
                            </p>
                        </div>
                        ',
                    ),
                    array(
                        'title'        => 'What do the winners have to say?',
                        'category'     => 'winners',
                        'date'         => '9/12/2013',
                        'published'    => TRUE,
                        'content'      => '
<img alt="" src="https://s3.amazonaws.com/GCNewsArticles/fabian_big.jpg"
     style="float: left; height: 250px; width: 217px; margin-right: 5px;"/>

<p><strong>Fabain Calallero created the video &ldquo;To Be&rdquo;:</strong> I was accepted to Hunter College in
    New York City earlier this year. During the last two years I have developed an interest in film, and
    recently decided it was time to go back to school, after a three-year absence, to pursue a career in the
    field. I had no idea how I was going to pay for this endeavor. My parents have no money and, due to my
    immigration status, I am not eligible to receive any federal aid. I began searching online for scholarships
    for which I was eligible and stumbled upon the <em>Got Chosen-Lights, Camera, Action Video Scholarship
        Contest</em>. It was a true blessing to find out about this scholarship. Not only was I eligible, but it
    also involved filmmaking and music, my two passions. I made a music video for one of my original songs and
    applied to the contest.</p>

<p>The hardest part of this process was not only creating the video, but also getting people to vote for it on a
    daily basis. The voting phase was the most nerve-wracking because you had to remain in the top 20 to stay
    alive in the contest. I was constantly checking the Got Chosen site to see what place I was in. The last day
    of the voting phase I went on the site to check my status and I was in 20th place. I posted the video online
    to get as many people as possible to vote in the last stretch and hoped for the best. When I checked my
    status on the Got Chosen site the morning after the voting phase ended, I was relieved to see that I was
    still in the top 20, barely hanging in there at 20th place. It was well worth the trouble because now I have
    money to pay for school, and I learned a bit about how to market myself online. Every vote counts in this
    contest. </p>

<p>I feel honored to have been chosen as the winner by the panel of judges. I could not believe it when I saw
    the e-mail notification. It has taken an immense load off my shoulders. I encourage everyone to apply for
    this. It is an amazing opportunity to use your art to get money for school. </p> <img alt=""
                                                                                          src="https://s3.amazonaws.com/GCNewsArticles/emina_big.jpg"
                                                                                          style="float: left; height: 216px; width: 320px; margin-right: 5px;"/>

<p><strong>Emina Sonnad created the video &ldquo;Rainbowfish-Here I am&rdquo;:</strong> When I came across
    GotChosen&rsquo;s video scholarship, it immediately seemed like the perfect choice for me, as I love making
    videos to express myself in creative ways. Even better, I&rsquo;m a singer and songwriter, so I had no
    shortage of material to use for the &lsquo;original music&rsquo; category. I grabbed my sister, my ukulele,
    a video camera, and a close friend and hiked to the top of a hill where we could film our song before
    sunset. That was the easy part. Next, I had to get votes. Using Facebook, email, Instagram, and old
    fashioned face-to-face begging, I explained my situation and asked for people to vote for me daily if they
    liked my song. We also posted the link frequently on our band&rsquo;s Facebook page and I was amazed at the
    amount of support we rallied. I think a lot of people were able to connect with the emotion behind the song,
    and I&rsquo;m incredibly grateful for every vote I got. Being chosen as a winner was such a thrill, and I
    would recommend this contest to everyone, because you definitely get what you put into it. </p> <img alt=""
                                                                                                         src="https://s3.amazonaws.com/GCNewsArticles/amanda_big.jpg"
                                                                                                         style="float: left; height: 216px; width: 320px; margin-right: 5px;"/>

<p><strong>Amanda Neuhouser created the video &ldquo;Waiting&rdquo;:</strong> I found out about GotChosen
    through my mom who was looking for scholarship opportunities that would suit me best. After looking at the
    GotChosen website, I decided this would be a great chance for me to get my skills out there and possible
    earn money in the making. After I found out I was one of the runner-up\'s I was very excited because I needed
    this money to help pay for all of my books in college. I started College this year at SIU Carbondale and I\'m
    majoring in film production. I encourage everyone to apply for this scholarship because it is super easy to
    apply and it will give you great exposure. </p>

<div style="clear:left">
    <img alt=""
             src="https://s3.amazonaws.com/GCNewsArticles/jake_big.jpg"
             style="float: left; height: 250px; width: 217px; margin-right: 5px; margin-top: 5px;"/>

    <p><strong>Jake Tuohy created the video &ldquo;We In The Building&rdquo;:</strong> As a student at Suffolk
        Community College, Jake heard about the contest from Fastweb and is excited to place as a runner up. He\'s
        planning to use the money for one of his most immediate college expenses - likely it will be for books.</p>
</div>

<div style="clear: left;"><img alt="" src="https://s3.amazonaws.com/GCNewsArticles/emily_big.jpg"
                               style="float: left; height: 216px; width: 320px; margin-right: 5px; margin-top: 5px;"/>

    <p><strong>Emily Hoven created the video &ldquo;Oh The Places You&rsquo;ve Gone&rdquo;:</strong> I found out
        about the contest while browsing Google for scholarships to apply for. I spend a lot of my time creating
        videos to post on YouTube, so this contest was right up my alley! In university, every little bit helps;
        this scholarship will help alleviate some of the stress that comes with paying tuition, which will allow
        me to focus more on my studies.</p></div>
</div>
                        ',
            ),
            array(
                'title'        => 'GotChosen congratulates July $1000 Every Month Scholarship Winner',
                'category'     => 'winners',
                'date'         => '8/27/2013',
                'published'    => TRUE,
                'content'      => '
                <p>
                    You have completed your education and now you have a new road to travel - unfortunately
                    for many graduates this means student loan debt.
                </p>
                <p>
                    <img alt="" style="width: 190px; margin-right: 15px; margin-bottom: 15px; display: block; float: left;" src="https://s3.amazonaws.com/GCNewsArticles/Arhonda_Tower.jpg" />
                    Our July $1000 Every Month Scholarship winner, Arhonda Tower (<a target="_blank" href="http://www.uwf.edu" re_target="_blank">University of West Florida</a>), has $1000 to help pay down
                    her student loans.
                    <br />
                    <br />
                    Arhonda shared "I was looking for an opportunity to return back to school and become
                    an educational specialist, but my student loans prevented that from happening. However,
                    I was determined and motivated to break through that barrier and decided to look
                    for scholarships to help reduce the debt.
                    <br />
                    <br />
                    I found the gotchosen.com scholarship through <a target="_blank" href="http://www.fastweb.com" re_target="_blank">
                        Fastweb.com</a>. It was simple and easy to do. I can\'t express how grateful
                    I am for receiving this scholarship. It gives me hope that I will be able to complete
                    my career dreams and become a positive influence".
                </p>
                <p style="clear: both;">
                </p>
                <p>
                    <img alt="" style="width: 280px; margin-left: 15px; display: block; float: right;" src="https://s3.amazonaws.com/GCNewsArticles/everest.jpg" />
                    Liane Pardo-Mansfield of <a target="_blank" href="http://north-orlando.everestuniversity.edu/" re_target="_blank">
                        Everest University</a>, hosted our July $1000 Every Month Scholarship drawing.
                    Ms. Pardo-Mansfield is the Director of Student Services at the North Orlando Campus.
                    Her relentless dedication to students was a perfect match for GotChosen\'s commitment
                    to helping college students too.</p>
                <p>
                    We extend our congratulations to Arhonda for her willingness to work hard and never
                    give up. We are confident her dreams will come true. Our scholarship programs are
                    very easy to register and apply for. Our dedication to your privacy means we will
                    never sell your data to anyone, ever.
                </p>
                <p>
                    At the home office we are continuing to work on new opportunities for scholarships
                    dollars. More details will be announced soon.
                </p>
                ',
            ),
            array(
                'title'        => 'GotChosen congratulates June $1000 Every Month Scholarship Winner',
                'category'     => 'winners',
                'date'         => '8/9/2013',
                'published'    => TRUE,
                'content'      => '<img alt="" src="https://s3.amazonaws.com/GCNewsArticles/katywide.jpg"        style="width: 618px; margin-left: auto; margin-right: auto; display: block;" />    <p>        College students tell us all the time - scholarships make a difference. Our June        $1000 Every Month winner, Katy Guetherman, is another student that knows every scholarship        counts.</p>    <p>        Katy is a student at <a href="http://www.mercer.edu" target="_blank">Mercer University</a>. She shared "I found out about GotChosen        through working in my university\'s bursar and financial aid office. Because I pay        for my education by myself, I applied to many scholarships in the hopes of winning        one. I am very excited about winning, and it will go towards my tuition bill in        the Fall. Thank you GotChosen!”</p>    <p>        In June, Janette Flacon of Uceda English School, partnered with GotChosen, to randomly        draw the winner. Ms. Flacon, as the School Manager, is dedicated to helping students        master English. Uceda has been working with national and international students        from their US locations since 1988.</p>    <img alt="" src="https://s3.amazonaws.com/GCNewsArticles/june_school.png"        style="width: 646px; margin-left: auto; margin-right: auto; display: block;" />    <p>        We are happy to welcome Katy to our winners circle. Our scholarship programs are        very easy to register and apply for. Our dedication to your privacy means we will        never sell your data to anyone, ever.    </p>    <p>        Working hard behind the scenes, in the next 2 months, we will be launching the most        unique scholarship opportunity ever. And we know more scholarship dreams will come        true.</p>',
            ),
            array(
                'title'        => 'GotChosen congratulates May\'s  $1000 Every Month Scholarship winner',
                'category'     => 'winners',
                'date'         => '6/27/2013',
                'published'    => TRUE,
                'content'      => '
                <p>
                    <img alt="" src="https://s3.amazonaws.com/GCNewsArticles/josh.jpg" style="width: 320px; padding-top: 10px; padding-left: 15px; float: right;" />
                    May was an exciting month for Misty Spurlock. Misty\'s was the winner of the May $1000 Every
                    Month Scholarship. Her name was randomly drawn by Josh Blackwell, Language Ministry Pastor
                    at <a href="http://www.firstorlando.com/" target="_blank" re_target="_blank">FirstOrlando.com</a>. Josh speaks
                    several languages and has a passion for those who are
                    living in places where a different language is spoken.  Josh was excited to be a part of
                    this event. He supports GotChosen\'s commitment to offer scholarship opportunities for US
                    and International students knowing this can make a difference for their educational dreams.</p>
                <br />
                <p>
                    <img alt="" src="https://s3.amazonaws.com/GCNewsArticles/misty13.jpg" style="width: 188px; padding-top: 10px; padding-right: 15px; float: left;" />
                    Misty, a student at <a href="http://www.chatfield.edu/" target="_blank" re_target="_blank">Chatfield College</a>
                    in St. Martin, Ohio shared &ldquo;I started my college
                    journey in January of 2013. As a 38 year old mother of two, this has been a big challenge.
                    A challenge financially, mentally and emotionally. I recently saw the GotChosen scholarships
                    listings on <a href="http://www.chegg.com" target="_blank" re_target="_blank">Chegg.com</a>. I knew if I won, it would be a great help towards my college
                    expenses. Thanks to GotChosen, I am one step closer to my career goal. I am thankful
                    they have become part of my college journey. A big thanks to everyone at GotChosen".</p>
                <p>
                    It is always a great day when we are drawing another scholarship winner. With 3 unique
                    scholarship programs, the team at GotChosen stays busy. And with a few more surprises
                    to be launched in the next few months - more scholarship dreams will come true.
                </p>',
            ),
            array(
                'title'        => 'GotChosen congratulates April\'s $1000 Every Month Scholarship winner',
                'category'     => 'winners',
                'date'         => '5/24/2013',
                'published'    => TRUE,
                'content'      => '
                <p>
                    <img alt="" src="https://s3.amazonaws.com/GCNewsArticles/anthony.jpg" style="width: 323px; padding-top: 10px; padding-right: 15px; float: left;" />
                    Anthony Portigiatti, Ph.D., president and Chancellor of <a href="http://www.fcuonline.com/" target="_blank">Florida Christian University</a> selected the winner of the
                    April\'s $1000 Every Month Scholarship. Ryan Wagner of Vista, CA was randomly drawn
                    using True Random Number generator at random.org. Ryan attends Palomar College,
                    in San Marco, CA and will be starting UC at San Diego in the fall.</p>
                <p>
                    Dr. Portigatti hosted GotChosen&rsquo;s team at their facilities and welcomed the opportunity
                    to participate in choosing the winner at their state-of-the art campus. GotChosen
                    is pleased to have this partnership culminate is the announcement of April\'s scholarship
                    winner.</p>
                <p>
                    <img alt="" src="https://s3.amazonaws.com/GCNewsArticles/ryan.jpg" style="width: 133px; padding-left: 15px; float: right;" />
                    Ryan shared that every penny makes a difference for college students "I had no idea
                    how I was going to pay for UC at San Diego and researched many options. Many funding
                    sources are not available to me because my parents were hit by the mortgage crisis/recession
                    and cannot contribute. It had been very stressful; winning the GotChosen scholarship
                    will help a lot. Thank you GotChosen."</p>
                <p>
                    Congratulations again to Ryan, it was GotChosen\'s privilege to contribute to his
                    college dreams.
                </p>
                ',
            ),
            array(
                'title'        => 'The Winner\'s Story from GotChosen\'s Video Scholarship',
                'category'     => 'winners',
                'date'         => '4/23/2013',
                'published'    => TRUE,
                'content'      => '<p> <img alt="" style="width: 168px; padding-top: 10px; padding-right: 15px; float: left;" src="https://s3.amazonaws.com/GCNewsArticles/Kirk.jpg" /> Hello! My name is Kirk Saechao and I am the winner of GotChosen&rsquo;s Lights, Camera, Action Video Scholarship. I am a second year student at San Diego State University, majoring in computer engineering. I found out about the scholarship entirely by chance. I am involved with a fraternity called Alpha Phi Omega. Every week, we host a general meeting and we have a segment dedicated to presenting the membership body with both an internship and a scholarship. It just so happened to be my week to find a scholarship so I logged onto my Zinch account. I was scrolling down the list full of different scholarships and saw &lsquo;GotChosen&rsquo;s Video Scholarship&rsquo; contest. Reluctantly, I pressed on the link and read the information about it. I had never really made short films before, but had been in charge of taking pictures and creating Recap Videos of events for many organizations on campus. The scholarship competition began about a month prior to my finding out about it, and there had been already over two hundred entries. My chances seemed slim at best. I wasn&rsquo;t going to enter at first but a little voice in my head told me to just give it a shot. There was nothing to lose!</p> <p> Many people don&rsquo;t know, but I have always been an avid gamer, so I wanted to create a story that would reflect that geeky side of my personality. I wanted to create a storyline that many people in the gaming world would be able to connect with; yet at the same time, be likeable by those who have never touched a video game. I opted to use an extremely popular massively multiplayer online role-playing game (MMORPG) called &lsquo;MapleStory&rsquo; that I used to play as a kid and most of my friends used to play when they were younger as well. The game encourages social networking and interaction between players. In the midst and chaos of slaying monsters and completing dungeons, players often start to develop feelings for other players in this online game. And thus, I stumbled upon the foundations of my story; a boy and a girl falling for one another in an online video game. Two days of filming and several hours of editing later, &lsquo;Maple Love Story&rsquo; was completed with the help of my roommate and two close friends.</p> <p> <img alt="" style="width: 277px; padding-top: 10px; padding-left: 15px; float: right;" src="https://s3.amazonaws.com/GCNewsArticles/Kirk2.jpg" /> But perhaps making the short film was the easy part, the hard part was to get people to actually watch and vote for my video. Throughout my college experience, I networked and made a point to meet as many people as possible. I am very deeply involved with many Asian-American groups on campus plus I am a member of a large national fraternity. I went out to these groups and asked them for their support in my endeavor to which they happily obliged. I posted my story and video link on my social networking websites and nothing could have prepared me for what was to come. By the end of the first week, the outpour of support I received was unbelievable. My news feed on social networking sites were being flooded with support for my video, friends began telling their friends from other schools to watch and support, and the views on my video were jumping by the hundreds. Employees over at Nexon, the company that created MapleStory, caught whiff of my story and video, and contacted me telling me that they had shared my video on their official Twitter and Tumblr accounts. Colleagues at school told me they heard about my cause and were voting every day. Before long, my submission shot up to first place and I was quite literally in shock. Down to the last 24 hours, the contest was neck and neck. I woke up many days in second place and would reclaim first by the time I had went to bed. I like to guess that I had a group of over two hundred people strong standing behind me the whole way. In the final hours, we rallied together and made a final push. When the polls closed at 8:59:59 on the west coast, I was still on top. It was such a surreal time in my life and was nothing short of a Cinderella story.</p> <p> The scholarship prize money is definitely nothing to scoff at. College students know that any amount of money, whether $20 or $500, is extremely helpful. The money would be used to ease the huge financial burden of college; it wouldn&rsquo;t be fair to my supporters to use it any other way. Also, the fact that I&rsquo;ll be able to sustain myself throughout summer means that I won&rsquo;t have to get a job, so I can catch up on my units in summer school and perhaps get an internship with reputable videography companies in the area. It&rsquo;ll also allow me start putting away my own money in hopes of saving up for better equipment so I can replace my outdated gear. This is the end of my story! </p> <p> Thanks again to GotChosen for such an enormous opportunity!</p> <p> By Kirk Saechao<br/><em>Winner of GotChosen\'s "Lights, Camera, Action" Video Scholarship.</em> </p>',
            ),
            array(
                'title'        => 'GotChosen awards its second GotScholarship $40K Give Away',
                'category'     => 'winners',
                'date'         => '4/10/2013',
                'published'    => TRUE,
                'content'      => '
                <p><img alt="" width="300" style="padding-top: 10px; padding-right: 15px; padding-bottom: 15px; float: left;" src="https://s3.amazonaws.com/GCNewsArticles/Craig_Domeck.jpg" /><em>Craig Domeck, Ed.D., Dean of </em><a href="http://www.pba.edu/orlando"><em>Palm Beach Atlantic University &ndash; Orlando</em></a><em> </em>&nbsp;selected the winner of the 2<sup>nd</sup> GotScholar &nbsp;ship $40K Give Away . Dr. Domeck<strong>&lsquo;s</strong> involvement with the GotChosen scholarship reflects his commitment to support the dreams of students looking for ways to pay for college that will reduce &nbsp;future financial burdens. </p>
                <p>The 2<sup>nd</sup> GotScholarship $40K winner was drawn using &nbsp;<a href="http://www.random.org/">www.random.org</a> with oversight&nbsp; from Kathy Voldseth, CPA, Manager, Assurance Services from the auditing firm<strong> </strong><a href="http://cfrcpa.com/">Cross, Fernandez and Riley, LLP</a><strong>. &nbsp;</strong>The True Random Number generator scientifically ensures every entry has an equal chance of winning. </p>
                <p><img alt="" width="223" style="padding-top: 10px; padding-bottom: 15px; padding-left: 15px; float: right;" src="https://s3.amazonaws.com/GCNewsArticles/carmela_and_daniel.jpg" />The results were a family affair. Carmela Guerriero-Cuccio was the randomly drawn winner of the 2<sup>nd</sup> &nbsp;$40,000 scholarship offered by GotChosen, Inc. Ms. Cuccio stated &ldquo;I am so thrilled that my name was drawn and even more ecstatic that I can transfer this prize to my cousin Daniel. He will make great use of this scholarship and absolutely deserves it.&rdquo; </p>
                <p>&nbsp;Why would someone give away $40,000 to a cousin? Daniel Guerriero of New Jersey, the final winner of the 2<sup>nd</sup> GotScholarship $40K Give Away learned about the scholarship while he was on <a href="http://www.fastweb.com/">Fastweb.com</a>. Daniel said &ldquo;I was looking at all of the possible scholarships I was eligible for I figured that I might as well give this scholarship a shot. It was really simple to sign up for the scholarship&rdquo;. </p>
                <p>When Daniel realized he could increase his chances of winning with sponsors, he sent some friends and family members invitations to support him. His cousin, Carmela, who took out student loans while pursuing her Masters, took the few minutes needed to apply since the winnings were transferable, and became his sponsor. </p>
                <p>After finding out he won, Daniel shared &ldquo;Carmela always has been very supportive of me in all of my endeavors ever since I was a little kid. Thanks to Carmela, I am now the proud winner of this coveted scholarship&rdquo;. At the next Guerriero family holiday there will be lots to talk about.</p>
                <p>GotChosen scholarship programs encourage families, teachers, coaches and friends to become sponsors so they can support participants&rsquo; college dreams. Asking for sponsors helps students articulate their plans to pay for a college education. And when the sponsor also applies for the scholarship, they are providing two additional entries for the participant.</p>
                <p><img alt="" width="262" style="padding-top: 10px; padding-right: 15px; padding-bottom: 15px; float: left;" src="https://s3.amazonaws.com/GCNewsArticles/Oz_Silva_and_Dean_Craig_Domeck.jpg" />Mr. Oz Silva, Founder and CEO of GotChosen, Inc., made his commitment to college education when his company was just starting &ldquo;we did not feel we could wait to &ldquo;make it&rdquo; before we started giving back&rdquo;. The burden of student loans debt is a global problem. For many students throughout the world, paying for college can be a bigger challenge than going to college.&nbsp; Mr. Silva is determined to change the financial future of current and prospective students nationally and internationally &ndash; one student at a time.</p>
                <p>GotChosen offers several scholarship opportunities. In addition to the GotScholarship, they recently launched the Lights, Camera, Action Video Scholarship which will award $3000 to the video with the most votes. The newest program is the GotChosen $1000 Every Month Scholarship. A new winner will be randomly chosen every month. All scholarship winnings must be used for educational expenses or repayment of current student loans.</p>
                <p>For more information about GotChosen, Inc. and how to enter its multiple scholarship programs visit www.gotchosen.com.</p>
                ',
            ),
            array(
                'title'        => 'The Winner\'s Story from the second GotScholarship',
                'category'     => 'winners',
                'date'         => '4/10/2013',
                'published'    => TRUE,
                'content'      => '
                <p><img alt="" width="150" style="padding-top: 10px; padding-bottom: 15px; padding-left: 15px; float: right;" src="https://s3.amazonaws.com/GCNewsArticles/carmela_cuccio.jpg" />One decision can change a life. Carmela Cuccio was asked by her cousin Daniel to sponsor him for a scholarship drawing. &nbsp;Even though there is a 20 year age difference, Ms. Cuccio shared they have always been close. Carmela knew well the burden of student debt, having used student loans to finance her Master&rsquo;s program. Ms. Cuccio stated &ldquo;GotChosen is doing something wonderful by offering students the opportunity to follow their dreams without continuing to incur further debt simply by entering a contest&rdquo;. </p>
                <p>Taking only a few short minutes, Carmela willingly became a sponsor for Daniel. She also applied for the scholarship, knowing it would be 100% transferable if she won. This decision gave Daniel two more entries for the scholarship and that was what he needed to ultimately become the final winner of the 2<sup>nd</sup> GotScholarship $40K Give Away.</p>
                <p><img alt="" width="168" style="padding-top: 10px; padding-right: 15px; padding-bottom: 15px; float: left;" src="https://s3.amazonaws.com/GCNewsArticles/daniel_winner.jpg" />Ms. Cuccio stated &ldquo;I am so thrilled that my name was drawn and even more ecstatic that I can transfer this prize to my cousin Daniel. He will make great use of this scholarship and absolutely deserves it.&rdquo;</p>
                <p>Daniel Guerriero of New Jersey received a call from his cousin Carmela. &nbsp;Daniel shared &ldquo;when she told me that she had an &ldquo;Easter gift&rdquo; for me, I was a little confused. She then told me the fantastic news. She had won this amazing scholarship and was transferring the prize to me. I remained speechless for a good five minutes only able to produce the words, Oh my,&rdquo; and he had to wait to hear it officially from GotChosen. </p>
                <p>When Daniel was asked how he learned about the GotScholarship he said &ldquo;I thought it was way too good to be true. However, while I was on FastWeb.com looking at all of the possible scholarships I was eligible for I figured that I might as well give this scholarship a shot. It was really simple to sign up for the scholarship&rdquo;. </p>
                <p><img alt="" width="150" style="padding-top: 0px; padding-bottom: 15px; padding-left: 15px; float: right;" src="https://s3.amazonaws.com/GCNewsArticles/happy_dance.jpg" />Daniel realized he could increase his chances of winning with sponsors, so he sent some friends and family members invitations to support him. This decision changed Daniel&rsquo;s life. &ldquo;I am one of the youngest members of the Guerriero family and hoping to become the first member of my immediate family with a college degree. &nbsp;This scholarship takes away a good amount of the financial burden of college and is something I will be truly grateful about for many years to come.&rdquo; </p>
                <p>&ldquo;Carmela always has been very supportive of me in all of my endeavors ever since I was a little kid. Thanks to Carmela, I am now the proud winner of this coveted scholarship&rdquo;. Daniel plans to complete his studies at University of South Carolina and continue on for a Masters in physical therapy.</p>
                <p>By Daniel Guerriero<br />
                <i>Winner of the 2<sup>nd</sup> GotScholarship $40K Give Away</i></p>
                ',
            ),
            array(
                'title'        => 'New Monthly Scholarship Program',
                'category'     => 'latest',
                'date'         => '4/09/2013',
                'published'    => TRUE,
                'content'      => '
                <p>GotChosen has announced an additional easy-to-apply for scholarship program. A monthly winner will receive $1,000 towards their educational expenses. Through this newly implemented drawing, GotChosen will be able to help a broader range of students every month. </p>
                <p>It is understood that students experience various reoccurring expenses such as materials from pencils to textbooks, as well as tutoring costs to monthly tuition installments while attending school. With the <em>GotChosen $1000 Every Month Scholarship</em> it is now possible to cover some of those common costs.</p>
                <p>The monthly program offers a free and simple solution to participants 18 years or older.&nbsp;Paticipants&nbsp;need to&nbsp;apply every month in order to be eligible for the current monthly drawing. </p>
                <p>keep a lookout for any additional announcements as GotChosen is preparing to launch yet another monthly program&nbsp;for the sponsors involved in the <em>3<sup>rd</sup> GotScholarship $40K Give Away. </em></p>
                ',
            ),
            array(
                'title'        => 'GotChosen awards its first GotScholarship $40K Give Away',
                'category'     => 'winners',
                'date'         => '10/29/2012',
                'published'    => TRUE,
                'content'      => '
                <p>David Fitzpatrick of Gaithersburg, MD wins the&nbsp;1<sup>st</sup> <a href="{{ path(\'scholarship\') }}">GotScholarship $40K to Give Away</a>. And GotChosen will continue its commitment to fulfill college dreams with the start of the 2<sup>nd</sup> GotScholarship $40K to Give Away with even more opportunities for future, current and graduated&nbsp;students.</p>
                <p><img alt="" width="384" height="229" style="padding-top: 5px; padding-right: 15px; padding-bottom: 5px; float: left;" src="https://s3.amazonaws.com/GCNewsArticles/drawing_selection.png" /> GotChosen partnered with Dr. Steve D. Whitaker, Ph.D., Head of School at <a href="http://www.thefirstacademy.org/">The First Academy</a>, in Orlando, Florida and Patrick Barrett, Director of Strategic Initiatives at The First Academy for the official drawing of the&nbsp;1<sup>st</sup>&nbsp;GotScholarship $40K To Give Away. </p>
                <p>The winner was determined using the True Random Number generator at <a href="http://www.random.org/">www.random.org</a>.&nbsp; This independent website is a cryptographically strong random number generator utilizing random chaotic atmospheric data. </p>
                <p>The process of generating true random numbers involves identifying little, unpredictable changes in the data. RANDOM.ORG uses little variations in the amplitude of atmospheric noise. The True Random Number generator ensured every entry has an equal chance of winning. </p>
                <p>David Fitzpatrick, the lucky winner, was randomly drawn by Dr. Whitaker to win the&nbsp;1<sup>st</sup> <a href="{{ path(\'scholarship\') }}">GotScholarship $40K to Give Away</a>. It was a simple and easy registration process that David completed at the GotChosen.com website. He increased his chances of winning by using social media and gathering sponsors. David had nine sponsors &ndash; this was equivalent to ten entries for the <a href="{{ path(\'scholarship\') }}">GotScholarship $40K to Give Away</a>.</p>
                <p><img alt="" style="padding-top: 10px; padding-right: 0px; padding-bottom: 15px; float: right;" src="https://s3.amazonaws.com/GCNewsArticles/davidlab.png" /> David states<em> &ldquo;Like many of you, I too registered for an account that organizes and notifies me of current scholarships available, which is how I stumbled across the GotChosen scholarship giveaway. My purpose for applying for this scholarship was probably the same as yours; to augment my financial aid and ease the burden of college debt on my family.&nbsp; </em></p>
                <p><em>Although the odds did not seem in my favor, I pushed myself to realize that this giveaway was chosen at random and that sponsors will increase my chances of winning&rdquo;.</em>&nbsp; And David&rsquo;s dream to pay for his education without the burden of student loans will now come true.</p>
                <p>David currently studies at Montgomery Community College in Maryland with plans to continue at University of Maryland. David will complete his courses in Environmental Science and Policy Major this year and now with his <a href="{{ path(\'scholarship\') }}">GotScholarship $40K to Give Away</a> winnings David shared <em>&ldquo;</em><em>This scholarship provides a tremendous opportunity for me to pursue my education goals in environmental science and policy&rdquo;</em></p>
                <p>GotChosen is an innovative startup in digital media currently headquartered in Orlando, FL. The company is developing an unparalleled interactive media and informational exchange experience for users. This unmatched interaction will give a purpose to prospective and current students and their families, alumni, and the educational institutions committed to impacting the world.&nbsp;</p>
                <p> An essential element of the core values of GotChosen is a deep-rooted commitment to give back. David is the first to benefit from the focus of making higher education available to more students.</p>
                <p style="margin: 0in 0in 10pt;">It&rsquo;s easy and free to&nbsp;<a href="{{ path(\'scholarship\') }}">register</a> and apply for the chance to win the&nbsp;2<sup>nd</sup> GotScholarship $40K to Give Away.</p>
                ',
            ),
            array(
                'title'        => 'The Winner\'s Story from the first GotScholarship',
                'category'     => 'winners',
                'date'         => '10/29/2012',
                'published'    => TRUE,
                'content'      => '
                <img alt="" style="padding-top: 15px; padding-right: 15px; padding-bottom: 15px; float: left;" src="https://s3.amazonaws.com/GCNewsArticles/davidnews.jpg" />
                <p>Hello everyone! My name is David Fitzpatrick and I am a junior attending Montgomery Community College in Maryland. First off, let me start out by saying how grateful and honored I am to be randomly chosen for this scholarship. </p>
                <p>Currently, I am studying as an Environmental Science and Policy Major and would like to finally transfer to the University of Maryland. Winning this scholarship will aid me in my self-perpetuating odyssey to help our planet.</p>
                Like many of you, I too registered for an account that organizes and notifies me of current scholarships available, which is how I stumbled across the GotChosen scholarship giveaway. My purpose for applying for this scholarship was probably the same as yours; to augment my financial aid and ease the burden of college debt on my family.&nbsp;&nbsp;&nbsp;
                <p>Although the odds did not seem in my favor, I pushed myself to realize that this giveaway was chosen at random and that sponsors will increase my chances of winning. For those of you who are familiar with the book <i>The Hunger Games </i>by Suzanne Collins, the only way to explain how I feel right now is to imagine that you&rsquo;ve emerged victorious from the Hunger Games with the help of your sponsors. </p>
                <p>This scholarship will certainly be put to good use towards my tuition, debt, and books. Additionally, I will hand down any money I do not use. This scholarship provides a tremendous opportunity for me to pursue my education goals in environmental science and policy. Thanks to this scholarship, I will do my absolute best to make a change and I will not let any of you down. </p>
                <p>Once again, I am truly grateful for this opportunity. I do not know if it was fate or luck; however, I do know you could win this scholarship too.</p>
                <p>By David Fitzpatrick<br />
                <i>Winner of the 1<sup>st</sup> GotScholarship $40K to Give Away</i></p>
                ',
            ),
            array(
                'title'        => 'Student Debt is a Worldwide Problem, GotChosen Resolves to Do Something About it',
                'category'     => 'latest',
                'date'         => '9/20/2012',
                'published'    => TRUE,
                'content'      => '
                <p style="margin: 12pt 0in 3pt;">With the registration period for the first GotScholarship: $<i>40K to Give Away</i> drawing ending on September 30th, GotChosen has even bigger plans in mind. &nbsp;While the participation of students and friends has been very successful, GotChosen CEO, Oz Silva, said &ldquo;I was intrigued by the diversity of website visitors from over 7,000 cities in over 100 countries." &nbsp;He added that many of the locations were ineligible for the scholarship. &ldquo;While student debt in the U.S. is greater than anywhere else, it is clear students and their families in other countries are searching for solutions too.&nbsp; <s></s></p>
                <p style="margin: 12pt 0in 3pt;">Although as part of phase 1 of the business plan, the <i>GotScholarship:</i> <i>$40K to Give Away</i> drawing was begun primarily as the GotChosen branding strategy while the company developed its innovative next generation services, Silva soon realized that the situation of college debt required a more comprehensive long term solution. &nbsp;Accordingly, Silva announced both more than 10 new scholarships and&nbsp;GotChosen international expansion to Canada, Mexico, Brazil, Columbia, and Chile.</p>
                <p style="margin: 0in 0in 10pt;"><b><br />
                        Scholarship and Website News</b>: To inaugurate phase 2 of the GotChosen business plan, a new $40K to Give Away drawing will begin in October, 2012, and will be available to participants in all six countries. &nbsp;Students in those countries will also be eligible to participate in a Video Contest Scholarship based on optional preselected subjects.&nbsp; There will also be two monthly scholarships contest (starting in January&nbsp; 2013), one for the North American bloc, and the other for the Latin American bloc.</p>
                <p style="margin: 0in 0in 10pt;">Also as part of phase 2, a completely revised GotChosen website will be published in October in English, Spanish, and Portuguese.&nbsp; The new GotChosen website will feature mobile-friendly multilingual registration and navigation features that will be implemented throughout October and November.</p>
                <p style="margin: 0in 0in 10pt;"><b>Summary: </b>GotChosen&rsquo;s roadmap began with the GotScholarship: $40K to Give Away drawing, the largest single private national scholarship initiative ever seen in the United States. &nbsp;Going international expands the market and more importantly, the benefits to be derived from GotChosen&rsquo;s growing portfolio of scholarships.</p>
                <p>Silva says, "The comments from students and others registering for our initial scholarship confirms how serious our educational and student loan situation is, and how important it is for everyone to try to do something about it. Fortunately, that\'s our business, and we look forward to helping as many individuals as possible over the coming years."</p>
                <p> For more information about GotChosen, please visit <a href="http://www.gotchosen.com/">www.gotchosen.com</a></p>
                ',
            ),
            array(
                'title'        => 'Social Networking Functionality with a Purpose',
                'category'     => 'latest',
                'date'         => '9/20/2012',
                'published'    => TRUE,
                'content'      => '
                <p style="margin: 0in 0in 10pt;">GotChosen will not duplicate existing social networking sites.&nbsp; Instead GotChosen will provide a new user interface that encourages enrolled students and potential students to interact with each other and with educational institutions both domestically and internationally.&nbsp; &nbsp;This will provide students with unprecedented opportunities to network and explore potential degree programs, research opportunities, athletics, and facilities available to them at universities all over the world.&nbsp; When students become contributors by responding to questions they open the door to new friends.&nbsp; Potential students can connect online before they move, so once they arrive on campus they may already know some fellow students, making the transition to college life a bit easier. </p>
                <p style="margin: 0in 0in 10pt;">To accomplish this GotChosen will create a searchable database to allow students to make inquires and utilize filters to focus on the information most important to them.&nbsp; This will make it possible to compare potential universities and programs to find the one most suited for them and their career choice.&nbsp; GotChosen will become a valuable service that will help students make informed decisions about their future.&nbsp; This social networking utility will offer outstanding results with real benefits.</p>
                <p style="margin: 0in 0in 10pt;">In addition to this remarkable database, we will also feature forums.&nbsp; These may include but are certainly not limited to subjects such as proprietary university scholarships, and discussions about the advantages and disadvantages of attending various schools, athletic programs, the merits of potential degree programs and the learning possibilities provided by studying abroad. &nbsp;&nbsp;</p>
                <p style="margin: 0in 0in 10pt;">GotChosen would like to make these outstanding tools available to as many students as possible.&nbsp; To accomplish this, the GotChosen website will feature mobile-friendly multilingual registration, navigation, and participation, as well as enhanced integration with Facebook and other social networking sites.</p>
                ',
            ),
            array(
                'title'        => 'Student Debt is a Worldwide Problem',
                'category'     => 'latest',
                'date'         => '9/20/2012',
                'published'    => TRUE,
                'content'      => '
                <p style="margin: 0in 0in 10pt;">While issues surrounding student debt regularly make headlines in the United States, they are equally serious in many other parts of the world. &nbsp;Even though the colossal size of the nearly $1 trillion dollars in U.S. student debt dwarfs the amount of student debt in other countries, the problems that student debt have created elsewhere are no less serious. </p>
                <p style="margin: 0in 0in 10pt;">In many countries, parents must co-sign all student debt. &nbsp;In others, the interest rate (about 10% in Mexico, for example) is far higher than in the U.S. &nbsp;In Chile, there are more than 100,000 student loan defaulters that owe an average of $5,400 each, which is about a third the country&rsquo;s annual per capita income.* There have been demonstrations and protests throughout the world, and the situation remains a challenge as economies everywhere face financial difficulties, and governments continue to reduce the amount of money they devote to support both educational institutions and student loans.</p>
                <p style="margin: 0in 0in 10pt;">* Ensino Superior, 7/8/2012</p>
                ',
            ),
            array(
                'title'        => 'GotChosen Receives Coverage on Yahoo! News!',
                'category'     => 'latest',
                'date'         => '5/4/2012',
                'published'    => TRUE,
                'content'      => '
                <p>GotChosen\'s scholarship initiative was recently picked up on Yahoo! News! To view the coverage please visit:</p>
                <p>&nbsp; <a href="http://news.yahoo.com/orlando-based-company-gotchosen-launches-largest-single-private-071205627.html">http://news.yahoo.com/orlando-based-company-gotchosen-launches-largest-single-private-071205627.html</a></p>
                <p>More news coverage coming soon!</p>
                <p>&nbsp;</p>
                ',
            ),
            array(
                'title'        => 'Orlando-based Company, GotChosen, Launches the Largest Single Private National Scholarship Initiative in the United States',
                'category'     => 'winners',
                'date'         => '5/2/2012',
                'published'    => TRUE,
                'content'      => '
                <p style="text-align: justify;"><strong>FOR IMMEDIATE RELEASE</strong></p>
                <p> </p>
                <p style="text-align: center;"><strong></strong></p>
                <p> </p>
                <p style="text-align: center;"><strong></strong></p>
                <p> </p>
                <p style="text-align: center;"><strong>Orlando-based Company, GotChosen, Launches the Largest Single Private National&nbsp;</strong></p>
                <p style="text-align: center;"><strong>Scholarship Initiative in the United States</strong></p>
                <p> </p>
                <p><strong></strong></p>
                <p> </p>
                <p><strong></strong></p>
                <p> </p>
                <p><strong>Orlando, FL, May 1, 2012 &ndash; </strong>Today,<strong> </strong>GotChosen, Inc. (www.gotchosen.com), an innovative digital media and development company headquartered in Orlando, FL, is launching the largest scholarship drawing in the nation titled, &ldquo;GotScholarship: $40K to Give Away!&rdquo;<em> </em>&nbsp;There is no cost to participate, and the company will award $40K to one winner with a dream of attending college, or to pay for existing college loans. The scholarship may also be transferred by the winner to an individual of any age to pay for educational expenses.</p>
                <p> </p>
                <p><strong></strong></p>
                <p> </p>
                <p>The concept for the company was conceived during the economic struggles of 2008 by Founder and CEO, Oz Silva.&nbsp;Silva&rsquo;s focus was to assist anyone with the desire of completing their degree in higher education, but who did not have the financial means to do so. Silva&rsquo;s philanthropic interest developed the first phase of the corporate vision beginning with the $40K scholarship drawing. The program will instantly establish GotChosen&rsquo;s primary mission of assisting individuals and families to achieve their educational potential and life aspirations, and to introduce the company on a large national scale. Subsequent drawings will enlarge the scope of the company&rsquo;s endeavors, but will remain focused on the college market.</p>
                <p> </p>
                <p> </p>
                <p>&ldquo;An essential element of the core values of GotChosen is a deep-rooted need and desire to give back,&rdquo; said Silva.&nbsp;&ldquo;Our team is eager to witness the wonderful things that will be done by the recipients, and to how this scholarship will change their futures. We hope that our donation to their dreams was the difference that made it all possible.&rdquo; </p>
                <p> </p>
                <p> </p>
                <p>The amount of student loans acquired in 2011 crossed the $100 billion mark for the first time in history, and total loans outstanding will exceed $1 trillion for the first time this year. The Federal Reserve Bank of New York, the U.S. Department of Education, and private sources report that Americans now owe more on student loans than on credit cards. &nbsp;</p>
                <p> </p>
                <p> </p>
                <p>As the cost of higher education continues to increase, the traditional channels for student loans and grants are being limited by ongoing credit tightening, economic weakness, and governmental policies. &nbsp;For some, this situation creates an insurmountable obstacle to a college education, making the $40,000 scholarship from GotChosen a new potential way for students to achieve their goals. &nbsp;</p>
                <p> </p>
                <p> </p>
                <p>The GotChosen scholarship is open to all: current students, previous students with outstanding loans, or even future college students. It is free and easy to enter, as well as being 100% transferable. In addition, the more individuals recruited to enter the contest on a student&rsquo;s behalf, the more potential entries will exist to increase the student&rsquo;s chances of winning.&nbsp;</p>
                <p><strong>You may register to enter for your chance to win the GotScholarship: $40K to Give Away Drawing by visiting the GotChosen website at: www.gotchosen.com beginning May 1, 2012 and the contest will close on September 30, 2012.</strong></p>
                <p> </p>
                <p><strong><span style="text-decoration: underline;"></span></strong></p>
                <p> </p>
                <p>&ldquo;This is only the first phase of our efforts,&rdquo; Silva further added. &ldquo;We anticipate creating a long line of scholarship recipients who will benefit.&nbsp; It is an exciting time for our young company, and we are hopeful that we will become a powerful creative and economic contributor for many years to come.&rdquo;</p>
                <p> </p>
                <p><strong><span style="text-decoration: underline;"></span></strong></p>
                <p> </p>
                <p><strong><span style="text-decoration: underline;">About GotChosen:</span></strong></p>
                <p> </p>
                <p><strong><span style="text-decoration: underline;"></span></strong></p>
                <p> </p>
                <p>GotChosen is an innovative startup in digital media and development headquartered in Orlando, FL. The company&rsquo;s primary aim is to deliver an unparalleled interactive media and entertainment experience to users by bridging gaps in digital and traditional media. This vision will be implemented on computers, tablets, smartphones and smart TVs in 2013-2014.</p>
                <p> </p>
                <p> </p>
                <p><strong>For more information about GotChosen, please visit: www.gotchosen.com.</strong></p>
                <p> </p>
                <p> </p>
                <p> </p>
                <p style="text-align: center;"><strong>###</strong></p>
                <p> </p>
                <p>&nbsp;</p>
                ',
            ),
            array(
                'title'        => 'News Section Coming Soon!',
                'category'     => 'winners',
                'date'         => '1/9/2012',
                'published'    => TRUE,
                'content'      => '
                <p>Hi everyone!</p>
                <p>Thank you for visiting the News section of GotChosen.com! We are looking forward to keeping you continually updated in the weeks to come with posts and information, media coverage and more, beginning with our first official announcement coming soon!</p>
                <p>In the meantime, please visit our new, official Facebook page at: www.facebook.com/GotChosen and "Like" to stay connected within this community as well.</p>
                <p>Thank you again and on behalf of the GotChosen News Team, we can\'t wait to meet you!</p>
                ',
            ),
            array(
                'title'        => 'GotChosen Congratulates the February $1000 Every Month Scholarship Winner',
                'category'     => 'winners',
                'date'         => '3/24/2014',
                'published'    => TRUE,
                'content'      => '
                <div>
                    <img src="https://s3.amazonaws.com/GCNewsArticles/Brian_gotchosen.jpg" alt=""
                         style="float:right; margin:0 8px 0 8px;" />

                    <p>
                        The 12th drawing for the Every Month Scholarship was hosted by
                        <a href="https://www.asburyseminary.edu/about/campuses/florida-dunnam-campus/" target="_blank">Asbury Theological Seminary</a>
                        at their Florida Dunnam Campus. Congratulation goes to Shania Lee of Michigan.
                    </p>
                    <p>
                        Ingrid McLennan, Director of Enrollment Management &amp; Student Services at Asbury, coordinated
                        the event. Ms. McLennan re-stated what is on their website "In one of the top tourist destinations,
                        worlds collide, cultures mix, and diversity is a fact of life. All of which make Orlando, FL, the
                        perfect setting for training and engaging tomorrow’s church leaders. The Florida Dunnam campus,
                        offers an opportunity to study with faculty from diverse backgrounds and extensive theological prowess.
                        This makes the Florida Dunnam campus culturally relevant for the growing multicultural, multi-ethnic
                        community in Florida and the Southeast".
                    </p>
                </div>

                <div style="clear:both;"></div>

                <div>
                    <img src="https://s3.amazonaws.com/GCNewsArticles/Shania_gotchosen.jpg" alt=""
                         style="float:left; margin:0 8px 8px 0;" />

                    <p>
                        Brian Banks, the Associate Director at Asbury drew the winner’s name using GotChosen’s platform
                        integration with Random.org. Mr. Banks is often on the road speaking to future students. He appreciated
                        the opportunity to be on campus to work with GotChosen and help make college dreams come true for
                        students seeking ways to fund their educational expenses.
                    </p>

                    <p>
                        The February winner, Shania, wrote about winning the $1000 Every Month Scholarship "I am very grateful
                        and blessed to have been awarded this money. It’s going to be of great use to me when I go to
                        <a href="http://www.gvsu.edu/" target="_blank">Grand Valley State University</a>. I definitely didn’t
                        expect to win when I read it was a drawing. I figured the odds were huge so I was shocked to receive an
                        email saying I was the winner.
                    </p>

                    <p>
                        I applied because college isn’t cheap. The more money you can get the better. The whole process to apply
                        was very easy. You make an account then your name is entered into the drawing. Now my books are covered
                        and I have GotChosen to thank for that".
                    </p>

                    <p>
                        GotChosen is pleased to support Shania. She made a small investment of time by registering and applying
                        for the GotChosen scholarships and received a substantial award for that investment.
                    </p>
                </div>
                ',
            ),
            array(
                'title'        => 'GotChosen Congratulates the January $1000 Every Month Scholarship Winner',
                'category'     => 'winners',
                'date'         => '2/19/2014',
                'published'    => TRUE,
                'content'      => '
                <div>
                    <img src="https://s3.amazonaws.com/GCNewsArticles/Erin_gotchosen.jpg" alt=""
                         style="float:left; margin:0 8px 8px 0;" />
                    <p>
                        If where you live is 25 degrees - a surprise in your inbox from Orlando would be a warm welcome.
                        That is how our January $1000 Every Month Scholarship winner, Erin Walters felt. Erin is studying
                        Food Service Management at <a href="http://www.jwu.edu/providence/" target="_blank">Johnson & Wales University in Providence</a>, RI.
                    </p>
                    <p>
                        Erin shared "I heard about this scholarship from <a href="http://www.fastweb.com/" target="_blank">Fastweb</a>.
                        I applied for the scholarship cause it was so easy to do and all applicants have an equal chance.
                        When I got the email saying I had won, I was very excited.
                    </p>
                    <p>
                        I am thankful to be a winner because I am studying abroad this summer and the scholarship will cover
                        a good portion of my programs costs. Thank you GotChosen for this wonderful opportunity."
                    </p>
                </div>

                <div style="clear:both;"></div>

                <div>
                    <img src="https://s3.amazonaws.com/GCNewsArticles/Heather_gotchosen.jpg" alt=""
                         style="float:right; margin:0 8px 8px 0;" />
                    <p>
                        GotChosen $1000 Every Month Scholarship was hosted at <a href="http://www.herzing.edu/orlando" target="_blank">Herzing University</a>
                        by Campus President, Antonacci. Ms. Antonacci\'s dedication to her students was her motivation to
                        support the GotChosen Scholarship programs.
                    </p>
                    <p>
                        Lauren Ruston, Director of Educational Funding- Orlando also played a key role in coordinating our event.
                        GotChosen is sincerely appreciative of their support.
                    </p>
                    <p>
                        Erin Walters will be thinking about her decision to apply for GotChosen\'s scholarship often, as she enjoys
                        her summer abroad program. As Erin said - "it was so easy" and taking a few minutes to apply for the drawing
                        has paid off.
                    </p>
                    <p>
                        We are eager to encourage all students to re-apply each month for the
                        <a href="https://www.gotchosen.com/en/monthly-scholarship" target="_blank">$1000 Every Month Scholarship</a>. You have
                        to be in it to win it and we do keep it easy and simple.
                    </p>
                    <p>
                        Congratulations to Erin. We wish her safe travels and the best abroad experience possible.
                    </p>
                </div>
                ',
            ),
            array(
                'title'        => 'GotChosen Congratulates the December $1000 Every Month Scholarship Winner',
                'category'     => 'winners',
                'date'         => '2/10/2014',
                'published'    => TRUE,
                'content'      => '
                <div>
                    <img src="https://s3.amazonaws.com/GCNewsArticles/Benjamin_Rhodes_gotchosen.jpg" alt=""
                         style="float:right; margin:0 0px 8px 8px;" />
                    <p>
                        Good things come to those who try. This is how Benjamin Rhodes felt when he was told he was the
                        winner of the December Every Month Scholarship.
                    </p>
                    <p>
                        Benjamin has big dreams and the GotChosen Scholarship will be part of the much needed help Benjamin will need.
                        Benjamin shared "I heard about this fantastic opportunity through <a href="http://www.fastweb.com/" target="_blank">Fastweb</a>
                        scholarships. I applied for the scholarship because it was super easy and didnt require much time".
                    </p>
                    <p>
                        The <a href="http://www.first.edu/" target="_blank">F.I.R.S.T. Institute</a> hosted the GotChosen drawing. In his
                        role as Instructor for Film and Video Production, Trent Duncan of F.I.R.S.T Institute, is an award winning filmmaker.
                    </p>
                    <p>
                        Trent was very committed to work with GotChosen in supporting the scholarships programs. GotChosen is
                        very appreciative of the help we received from Trent and his team.
                    </p>
                </div>

                <div style="clear:both;"></div>

                <div>
                    <img src="https://s3.amazonaws.com/GCNewsArticles/Trent_gotchosen.jpg" alt=""
                         style="float:left; margin:0 8px 8px 0;" />
                    <p>
                        Special thanks to Michelle Hill, <a href="http://www.first.edu/programs/film-video/faculty" target="_blank">Program Director
                            of Film and Video Production</a> for coordinating the event.
                    </p>
                    <p>
                        Benjamin told us after he heard he won "I am so thankful for GotChosen for investing my future!  I was born
                        and raised in Fort Worth, Texas. I plan on studying biology at <a href="https://www.unt.edu/" target="_blank">
                            University of North Texas</a> and attending medical school to become a radiologist.
                    </p>
                    <p>
                        I have been working my way up in the medical field. I started working as a Lifeguard at the YMCA and recently just
                        attended the Congress of Future Medical Leaders in Washington D.C. With this scholarship I will be able to pay for
                        my tuition and books. I am so glad to receive this scholarship and be financially sound. When I got the news that I
                        won the scholarship I was stunned. I feel truly blessed by this opportunity. Thank you".
                    </p>
                    <p>
                        Benjamin - GotChosen is excited to be able to help you with your educational expenses. We wish you a successful
                        journey. And look forward to being able to call you Doctor Rhodes.
                    </p>
                    <p>
                        Currently GotChosen has three scholarship opportunities. We are proud to share our scholarships are free, easy,
                        private, and open to students and student loan holders. Let us help you by registering and applying for our scholarships today.
                    </p>
                </div>
                ',
            ),
            array(
                'title'        => 'GotChosen Congratulates the November $1000 Every Month Scholarship Winner',
                'category'     => 'winners',
                'date'         => '1/2/2014',
                'published'    => TRUE,
                'content'      => '
                <div>
                    <img src="https://s3.amazonaws.com/GCNewsArticles/GotChosen_November_scholarship.jpg" alt=""
                         style="float:right; margin:0 8px 8px 0; height: 190px;" />
                    <p>
                    GotChosen awards its 9th $1000 Every Month Scholarship to Marlana Hanner, a student at Amberton University
                    in Garland, TX. Once again <a href="http://www.everest.edu/campus/north_orlando">Everest University</a> partners
                    with GotChosen to host the drawing at their North Orlando campus.
                    Marlena Stefanek, Medical Administrative/Medical Assistant Academic Program Director proudly represented
                    Everest at the drawing.</p>

                    <p>Our winner, Marlana Hanner is working on her Masters in Business Administration. She was very excited
                        to learn her name was randomly drawn as the November winner. </p>

                </div>
                <div style="clear:both;"></div>

                <div>
                    <img src="https://s3.amazonaws.com/GCNewsArticles/Marlana.jpg" alt=""
                         style="float:left; margin:0 8px 8px 0; height:200px;" />
                <p>Marlana shared after finishing her Bachelor\'s in Finance from Auburn University she wanted to go
                    straight into a Master\'s Program but was unable to afford it without accruing additional student loan debt. </p>
                    <p>Marlana stated "it had been five years and I knew if I wanted to go back to school I needed
                        to get started before I had the expense of a family; I went ahead and started my Master\'s knowing
                        God would provide. I was about to drop my winter courses to cover the additional expenses we were
                        having during the holidays. I had been applying for scholarships at fastweb.com. Fortunately, I had
                        applied for a monthly scholarship with GotChosen. When I received an e-mail that I had won the
                        November Scholarship I was so excited and grateful. I can\'t thank GotChosen enough for this
                        scholarship program! I would like to encourage everyone to apply for scholarships with GotChosen."
                        </p>
                </div>
                <div style="clear:both;"></div>

                <div>
                <p>GotChosen continues to be excited about our scholarship programs. We have awarded $134,000 in scholarships
                    and we plan to launch 2 more scholarship programs in early 2014. Our scholarships are easy, open
                    and your privacy is protected. Our commitment At GotChosen <em>-we respect your privacy. We will never
                    sell your data to anyone, ever.</em></p>

                    <p>
                    Have you registered and applied for our scholarships?
                    </p>
                </div>
                ',
            ),
            array(
                'title'        => 'GotChosen Congratulates the October $1000 Every Month Scholarship Winner',
                'category'     => 'winners',
                'date'         => '12/23/2013',
                'published'    => TRUE,
                'content'      => '
                <div>
                    <img src="https://s3.amazonaws.com/GCNewsArticles/Hines.jpg" alt=""
                         style="float:left; margin:0 8px 8px 0; height: 175px;" />
                    <p>
                    Rajohn Hines came across GotChosen from our <a href="https://www.facebook.com/GotChosen?ref=hl">Facebook</a> page.
                    He stated "I liked their page and followed it for a few months. After seeing their posts about
                    scholarship awards, I was eager to apply for myself. </p>

                    <p>I have over 40K in student loan debt that I wouldn’t mind getting help, so decided to do
                        what was necessary to be entered into the monthly drawing". </p>

                    <p>Rajohn Hines was the randomly chosen as the winner for our October $1000
                        Every Month Scholarship. His name was drawn by Paul Vowinkel, Academic Dean at
                        <a href="http://www.everest.edu/campus/north_orlando">Everest University, North Orlando Campus</a>. </p>


                </div>

                <div style="clear:both;"></div>

                <div>
                    <img src="https://s3.amazonaws.com/GCNewsArticles/GotChosen_Everest_University2.jpg" alt=""
                         style="float:right; margin:0 8px 8px 0" />
                    <p>Dean Vowinkel is very aware of the challenges for college students funding their educational
                        dreams. His support of GotChosen\'s efforts to make a difference reflects his own vision to help students.</p>

                    <p>Rajohn knows the need for dedication to complete his education.  He wanted to apply that same effort
                        to pay off his student loans. As he learned about the GotScholarship $40K Give Away, he told us "a thing
                        like this is definitely motivation for people like me, paying student loan debt or for others preparing
                        to go back to school.  I also have been trying to get sponsors to increase my chances for the $40,000
                        award in early 2014. It was a really easy process and I have recommended all of my college friends to
                        sign up and apply. Thanks to GotChosen, I have $1000 less to pay back in student loan debt”.
                        </p>
                </div>

                <p>GotChosen will continue to help students and those who have student loans. We believe in giving a chance
                    to students and their families to follow their dreams.  Congratulations again to Rajohn.</p>
                ',
            ),
            array(
                'title'        => 'GotChosen Congratulates the third $40K GotScholarship Winner',
                'category'     => 'winners',
                'date'         => '10/30/2013',
                'published'    => TRUE,
                'content'      => '
                <p>It is the drive to “Do Good” that continues to be GotChosen’s inspiration to help students like Jaime
                    McElyea, our 3rd $40K GotScholarship Give Away winner.</p>

                <img src="https://s3.amazonaws.com/GCNewsArticles/Jaime1.jpg" alt="Jaime 1"
                        style="margin-bottom: 8px;" />

                <p>When we informed Jaime she was the winner, her genuine surprise and disbelief was apparent. We asked
                    how she learned about our scholarship programs. Jaime said “I discovered the GotChosen scholarship
                    while searching through a long list of scholarships on Fastweb.com.”</p>

                <p>Jaime went on to say the $40K Give Away was perfect because “upon discovering how simple it was to
                    apply for the scholarship on the GotChosen website, I decided to give it a shot. All I had to do was
                    create an account. Like many others, I had applied for as many scholarships as possible in order to
                    help ease the financial burden of attending college.”</p>

                <div>
                    <img src="https://s3.amazonaws.com/GCNewsArticles/Jaime2.jpg" alt="Jaime 2"
                         style="float:right; margin:0 0 8px 8px" />
                    <p>As a junior at The University of Oklahoma, Jaime is in the Nursing Program. Jaime said "Growing up I
                        always wanted to work in the medical field and help people, which is why I chose my degree in
                        nursing. As a future healthcare employee I understand the need to further my education in order to
                        be able to give the best care possible.”</p>
                </div>

                <p>With more than half her college courses completed for her BSN, Jaime is now even more excited,
                    because “Thanks to GotChosen I now have an amazing opportunity. Receiving this scholarship in the
                    latter half of my education will not only allow me to complete my bachelor’s degree, but will be
                    applied towards a master’s in nursing as well.”</p>

                <p>GotChosen is now a true partner with Jaime. For the next four years the GotChosen team will be
                    encouraging and supporting Jaime. We are confident; with her dedication she will bring her knowledge
                    and passion to those in need of quality healthcare.</p>

                <p>Jaime wants to say “I applaud GotChosen for their scholarship program and providing others an
                    opportunity to complete their degree and further their educations.” We want to say we applaud her
                    and every participant in our scholarship programs. Every student is teaching us and the rest of the
                    world to never give up.</p>

                <div>
                    <img src="https://s3.amazonaws.com/GCNewsArticles/JoyBrastrom.jpg" alt=""
                         style="float:left; margin:0 8px 8px 0" />
                    <p>GotChosen knows paying for college can be as challenging as succeeding in college for many students
                        today. Understanding this financial reality, Joy Brastrom, Director of Education, representing
                        Anthem College (Orlando Campus) hosted the September random drawing. Ms. Brastrom and her staff,
                        instructors and administrators are exceptional in supporting their students\' college dreams and
                        career paths.</p>
                </div>

                <p>GotChosen would like to extend our thanks to Anthem College and Ms. Brastrom for making the
                    scholarship drawing an extraordinary event.</p>

                <div style="clear:left">
                    <p>At GotChosen we proudly announce we have already started our 4th GotScholarship $40K Give Away. And
                        we encourage everyone to apply!</p>
                </div>
                ',
            ),
            array(
                'title'        => 'GotChosen Congratulates the September $1000 Every Month Scholarship Winner',
                'category'     => 'winners',
                'date'         => '10/25/2013',
                'published'    => TRUE,
                'content'      => '
                <p>Our <a href="{{ path(\'monthly_scholarship\') }}">$1000 Every Month Scholarship</a>
                    winner deserves the full spotlight. Sergeant Scott Higgins is
                    currently deployed in Afghanistan, serving our country and ensuring our freedom. It is with pride
                    GotChosen will be supporting SGT Higgins pursue his Masters.</p>

                <div>
                    <img src="https://s3.amazonaws.com/GCNewsArticles/Scott1.jpg" alt="Scott 1"
                            style="float:left; margin:0 8px 8px 0" />
                    <p>Scott shared with us, "when I began my mission to further my education in 2008 I had a goal to obtain
                        my Masters in Business
                        Administration within 5 years. With years of deployments and training I had to take breaks between
                        classes in order to focus on my training and missions. When returning back in 2011 for an extended
                        period of time I was able to bolster my efforts and attend school full time. As I continued my
                        education I realized that my yearly Federal Tuition Assistance was not going to cover my yearly
                        school expenses forcing me to pay out of pocket. In trying to save money for a family I started
                        looking for alternatives to assist in paying for school. After months of researching internet sites
                        and companies I had created a few accounts with reputable sites. One
                        site led me to GotChosen.com where I could easily apply for scholarships continuously.</p>
                </div>

                <div style="clear:left">
                    <img src="https://s3.amazonaws.com/GCNewsArticles/Scott2.jpg" alt="Scott 2"
                            style="float:right; margin:0 0 8px 8px;" />
                    <p>For the past 2 years with the lack of government budget bills being passed on time many to include
                        myself are left waiting for funding or paying out of pocket. This year I was once again left paying
                        for expenses out of pocket. Luckily with the awarding of this scholarship I can continue to my
                        coursework without incurring more debt during these economically uncertain times. I do not possess
                        the verbal diction to describe my gratitude as this scholarship will allow me to continue my
                        education without another long break.</p>
                </div>

                <p>I am closing in on my next “mile mark” in my coursework and was dreading the upcoming break I was
                    going to be forced to take. Thanks to GotChosen.com I can continue my classes uninterrupted".</p>

                <p>It is always a great day for the GotChosen team to travel to a local school to randomly draw the next
                    scholarship winner. <a href="http://www.anthem.edu/orlando-florida/">Anthem College - Orlando Campus</a>
                    was the host school for our September drawings.
                    Working with their administrators, who are very commitment to each member of their student body, was
                    an excellent blend of our missions. GotChosen would like to extent our thanks to Anthem College and
                    Valerie Rodriquez for making the scholarship drawing so special.</p>

                <p>GotChosen congratulates Sergeant Scott Higgins on winning and more importantly wishes protection and
                    a safe return to his family in the USA soon.</p>
                ',
            ),
            array(
                'title'        => 'GotChosen Congratulates the August $1000 Every Month Scholarship Winner',
                'category'     => 'winners',
                'date'         => '10/3/2013',
                'published'    => TRUE,
                'content'      => '
                <p>It was very exciting to go to the <a href="http://www.first.edu">F.I.R.S.T Institute</a>,
                    (Florida Institute of Recording, Sound and
                Technology) surrounded by the most up-to-date technology for film and recording students. The photos
                tell the story of this school\'s commitment to give students the real life and hands-on experience to
                enter the workforce prepared.</p>

                <img src="https://s3.amazonaws.com/GCNewsArticles/first.jpg"
                        style="margin-bottom: 8px;" />

                <div>
                    <img src="https://s3.amazonaws.com/GCNewsArticles/michelle_hill.jpg" alt=""
                            style="float:right; margin:5px;" />
                    <p><a href="http://www.first.edu/programs/film-video/faculty">Michelle Hill</a>,
                        Program Director, Film and Video Program, our partner from F.I.R.S.T Institute, drew the
                    August scholarship winner, Ashley Hughes in a random drawing. Ms. Hill\'s support of our scholarship
                    program is a direct reflection of her commitment to support the dreams of students to advance their
                    education.</p>

                </div>

                <p>Ashley was so thrilled to learn her name was drawn and shared this story about her experience. "I am
                definitely blessed to be able to receive the GotChosen $1000 Every Month Scholarship!</p>

                <div>
                    <img src="https://s3.amazonaws.com/GCNewsArticles/ashleyj.jpg" alt=""
                            style="float:left; margin:5px;" />
                    <p>I saw the scholarship online at <a href="http://www.fastweb.com">fastweb.com</a>
                        and couldn\'t believe all you had to do was create a profile,
                    and then you were automatically entered into the drawing.</p>
                </div>

                <p>So I decided to apply for the scholarship since it was so easy and I definitely needed the money for
                college expenses. When I was notified that my name was chosen I really was astonished because it was so
                easy and I didn\'t do anything, but create a profile on their website. Thank you GotChosen Inc. for
                giving me this wonderful opportunity". Ashley is studying at the
                    <a href="http://www.georgiahealth.edu/medicine/about.html">Medical School of Georgia</a>.</p>

                <p>By the end of 2013 GotChosen will have awarded $138,000 in scholarships. We want you to have an
                opportunity to be part of our incredible scholarship program. We keep it simple and we will be adding
                more scholarships in late fall.</p>

                <p>Take a minute to register and apply. Visit us every month - it could be the best website you go to - if
                you love scholarship money, fun games, creating videos and the website dedicated to protecting your
                privacy.</p>
                ',
            ),
            array(
                'title'        => 'GotChosen announces the Winners for Season Two Lights Camera Action Video Contest',
                'category'     => 'winners',
                'date'         => '10/30/2013',
                'published'    => TRUE,
                'content'      => '
                <p>What happens when you combine talent, originality and determination? For our scholarship winners it means success and scholarships dollars for their college education.</p>
                <p>GotChosen\'s Season Two Video Scholarship Contest participants had 12 weeks to upload a video of original work and then get as many votes as possible to reach the Top 20. </p>
                <p>The Judging Panel, comprised of experts in all forms of the art, film and media gathered at Everest College (North Orlando Campus, Film Video Department, Matt Gunter, Chair) to view each of the Top 20 Videos. </p>
                <p>Representing the Panel of Judges:
                Patrick Kahn (<a href="http://snaporlando.com/" target="_blank" re_target="_blank">SNAP! Orlando</a>),
                Felicia Rieman (Award winning television producer),
                Attorney Jim Lussier (<a href="http://www.orlandoslice.com/" target="_blank" re_target="_blank">Orlando Slice</a>),
                Patrick Jackson (<a href="http://www.pbjentertainmentgroup.com/Home_Page.html" target="_blank" re_target="_blank">PB&amp;J Entertainment Group</a>),
                Realdo Manes (<a href="http://www.jornalbb.com/" target="_blank" re_target="_blank">Journal B&amp;B Newspaper</a>),
                Darko Cesar (Animator Instructor at <a href="http://www.fullsail.edu/" target="_blank" re_target="_blank">Full Sail University</a>),
                Mirjana Cesar (<a href="http://mirjanacesar.weebly.com/index.html" target="_blank" re_target="_blank">Mixed Media artist</a>),
                Rupert Meghnot (Instructor at <a href="http://www.fullsail.edu/" target="_blank" re_target="_blank">Full Sail University</a>),
                Michelle Hill - Program Director of Film and Video Program at <a href="http://www.first.edu/" target="_blank" re_target="_blank">F.I.R.S.T Institute</a>,
                Grazyna Kleinman - <a href="http://grazmagicphoto.com/" target="_blank" re_target="_blank">photographer</a>,
                John Ribeiro (musician),
                Nivaldo Nassiff &amp; Josh Blackwell (International Pastors at <a href="http://www.firstorlando.com/" target="_blank" re_target="_blank">First Baptist Orlando</a>).</p>
                <div style="width: 600px; height: 225px; margin: 0px auto; text-align: center;">
                <div style="width: 116px; height: 165px; float: left; margin: 2px;">
                <p>Fabian Caballero</p>
                <img alt="" src="https://s3.amazonaws.com/GCNewsArticles/fabian.jpg" style="height: 135px; width: 116px;" />
                <p>Winner</p>
                </div>
                <div style="width: 116px; height: 165px; float: left; margin: 2px;">
                <p>Emily Hoven</p>
                <img alt="" src="https://s3.amazonaws.com/GCNewsArticles/emily.jpg" style="height: 135px; width: 116px;" />
                <p>1st Runner-up</p>
                </div>
                <div style="width: 116px; height: 165px; float: left; margin: 2px;">
                <p>Emina Sonnad</p>
                <img alt="" src="https://s3.amazonaws.com/GCNewsArticles/emina.jpg" style="height: 135px; width: 116px;" />
                <p>2nd Runner-up</p>
                </div>
                <div style="width: 116px; height: 165px; float: left; margin: 2px;">
                <p>Amanda Neuhouser</p>
                <img alt="" src="https://s3.amazonaws.com/GCNewsArticles/amanda.jpg" style="height: 135px; width: 116px;" />
                <p>3rd Runner-up</p>
                </div>
                <div style="width: 116px; height: 165px; float: left; margin: 2px;">
                <p>Jake Tuohy</p>
                <img alt="" src="https://s3.amazonaws.com/GCNewsArticles/jake.jpg" style="height: 135px; width: 116px;" />
                <p>4th Runner-up</p>
                </div>
                </div>
                <div style="clear: left;">
                    <p>The $3000 Grand Prize Winner was awarded to Fabian Caballero of Hunter College with the most votes by the Panel
                        of Judges. Our four Runner-ups receiving a $250 scholarship award are: Emily Hoven (University of Alberta
                        Canada), Emina Sonnad (University of California, Berkeley), Amanda Neuhouser (Southern Illinois University) and
                        Jake Tuohy (Suffolk Community College). Watch their videos here (link to season two tab).</p>

                    <p>Congratulations to the winners. Their "never give up&rdquo; attitude and their efforts have paid off. We look
                        forward to our Season 3 Lights, Camera, Action, Video Scholarship contest to launch in late fall. Please check
                        our website for announcements and details coming soon.</p>
                </div>
                ',
            ),
        );

        $articleRepo = $manager->getRepository('GotChosenSiteBundle:NewsArticle');

        foreach ( $news as $article ) {
            if ( !$articleRepo->findOneBy(['title' => $article['title']]) ) {
                $o = new NewsArticle();
                $o->setTitle($article['title']);
                $o->setCategory($catRepo->findOneBy(['shortName' => $article['category']]));
                $o->setDateCreated(new \DateTime($article['date']));
                $o->setLastModified(new \DateTime($article['date']));
                $o->setPublishDate(new \DateTime($article['date']));
                $o->setPublished($article['published']);
                $manager->persist($o);
                $manager->flush();

                $c = new NewsArticleContent();
                $c->setContent($article['content']);
                $c->setArticle($o);

                $manager->persist($c);
            }
        }

        $manager->flush();
    }
}
