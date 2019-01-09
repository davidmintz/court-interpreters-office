## Notes to Self

19-Apr-2017

At this stage of the project, we attach an InterpreterEntityListener to the Doctrine entity manager when the InterpretersController is instantiated. For kicks we have also made the InterpreterEntityListener itself EventManagerAware so it in turn can trigger events.

For the record, this is with a view towards doing things with [Vault](https://vaultproject.io) and encryption/decryption of interpreter dobs and ssns.

This has produced mysterious side effects that affect testing. Things were blowing up until we made the `FixtureManager::getEntityManager()` attach the entity listener to the entity manager. We did that by stuffing into the same physical file a minimal class that extends `AbstractTestCase` and using it to get at the application's ServiceManager and pull the entity listener therefrom.

Things were still blowing up as the authentication does not seem to persist following a call to `login()`. Formerly it was sufficient to do this in the `setUp()` method but now we have to do it again, evidently following every call to `FixtureManager::getEntityManager()`. IOW the hypothesis is that now  `FixtureManager::getEntityManager()` blows away the authentication. Why? No clue.

Other note to self:  when you get a fatal "no document registered" error when doing things with Zend\Dom it may be because the response status was 303 and the response body is an empty string.
