<?php
class Cerb5BlogEventConditionAssignmentListener extends DevblocksEventListenerExtension {
	function __construct($manifest) {
		parent::__construct($manifest);
	}

	/**
	 * @param Model_DevblocksEvent $event
	 */
	function handleEvent(Model_DevblocksEvent $event) {
        $from_context = $event->params['from_context'];
 		
		switch($event->id) {
			case 'context_link.set':
                if(CerberusContexts::CONTEXT_WORKER == $event->params['to_context']) {
                    $worker_id = $event->params['to_context_id'];
                    switch($from_context) {
                        case CerberusContexts::CONTEXT_TICKET:
                            $ticket_id = $event->params['from_context_id'];
                            $ticket = DAO_Ticket::get($from_context_id);
                       		// Events
                            //Event_MailReceivedByGroup::trigger($message_id, $group->id);

                            // Trigger Worker Owner
                            if ($ticket->owner_id == $worker_id)
                                Event_Cerb5BlogOwnerAssigned::trigger($ticket->first_message_id, $ticket_id);
                            
                            // Trigger Worker Watchers
                            $context_watchers = CerberusContexts::getWatchers(CerberusContexts::CONTEXT_TICKET, $ticket_id);
                            if(is_array($context_watchers) && !empty($context_watchers))
                                foreach($context_watchers as $watcher_id => $watcher) {
                                    Event_Cerb5BlogTicketWatchersAssigned::trigger($ticket->first_message_id, $watcher_id);
                                }
                            break;
                        case CerberusContexts::CONTEXT_TASK:
                            $task_id = $event->params['from_context_id'];
                            $context_watchers = CerberusContexts::getWatchers(CerberusContexts::CONTEXT_TASK, $task_id);
                            if(is_array($context_watchers) && !empty($context_watchers))
                                foreach($context_watchers as $watcher_id => $watcher) {
                                    Event_WatchersAssignedToWorker::trigger($task_id, $watcher_id);
                                }
                            break;
                    }	
                }
				break;
		}
	}

    
};

