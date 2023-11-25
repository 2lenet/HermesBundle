<?php

namespace Lle\HermesBundle\Service\MailError;

class MailAnalyzer
{
    public const REGEX_LIST = [
        '/5\.1\.0 Address rejected .*/',
        '/.* Mailbox currently suspended - Please contact correspondent directly/',
        '/.* MAILBOX FULL./',
        '/.* Connected to/',
        '/.* Recipient address rejected/',
        '/.* Sorry, I couldn\'t find any host by that name\. .*/',
        '/4\.2\.1 mailbox temporarily disabled: .*/',
        '/451 Could not load DRD for domain/',
        '/451 Temporary local problem - please try later/',
        '/452 .* Mailbox size limit exceeded/',
        '/5\.1\.1 - Invalid mailbox: .*/',
        '/5\.1\.1 .*/',
        '/5\.1\.6 recipient no longer on server: .*/',
        '/5\.2\.0 .* Message identified as SPAM -/',
        '/5\.2\.1 .*/',
        '/5\.2\.1 .*... Addressee unknown, relay=.*/',
        '/5\.4\.1 .*/',
        '/5\.5\.0 .*/',
        '/5\.7\.1 .*/',
        '/550 .*/',
        '/550 MAILBOX NOT FOUND/',
        '/551 not our customer/',
        '/552 .*... User exceeds storage quota/',
        '/553 .*   delete user and bounce/',
        '/554 .*: Relay access denied/',
        '/554 no valid recipients/',
        '/554 Spam detected/',
        '/554 Your email is considered spam .*/',
        '/Address was not found. .*/',
        '/by that name .*/',
        '/cuda_nsu User .* not found./',
        '/delivery error: dd Sorry your message to .* cannot be delivered. This account has been disabled or discontinued .*. -/',
        '/delivery error: dd This account has been temporarily suspended. Please try again later. - .*/',
        '/delivery error: dd This user doesn\'t have a .* .* .* - .*/',
        '/delivery error: dd This user doesn\'t have a yahoo.com account .*/',
        '/Delivery to the following recipients failed. .*/',
        '/mailbox .* is restricted .*/',
        '/mailbox is full: retry timeout exceeded/',
        '/Mailbox unavailable or access denied - .*/',
        '/No relaying allowed/',
        '/No such person at this address/',
        '/recipient .* was not found in .*/',
        '/Requested action not taken: mailbox unavailable/',
        '/Requested action was not taken because this server doesn\'t handle mail for that user/',
        '/Rule imposed mailbox access for .* refused/',
        '/Sorry, I couldn\'t find any host named .*. .*/',
        '/Sorry, I wasn\'t able to establish an SMTP connection. .*/',
        '/sorry, mail to that recipient is not accepted .*/',
        '/Sorry, no mailbox here by that name. .*/',
        '/sorry, that domain isn\'t in my list of allowed rcpthosts .*/',
        '/Status: 4\.2\.2/',
        '/We would love to have gotten this email to .*/',
        '/Undelivered Mail Returned to Sender/'
    ];

    public function isErrorMail(string $subject): bool
    {
        foreach (MailAnalyzer::REGEX_LIST as $regex) {
            if (preg_match($regex, $subject)) {
                return true;
            }
        }

        return false;
    }
}
